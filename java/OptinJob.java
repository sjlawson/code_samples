package com.bashpole.updentity.webresources;

import java.io.File;
import java.io.FileWriter;
import java.io.IOException;
import java.io.PrintWriter;
import java.net.HttpRetryException;
import java.net.MalformedURLException;

import org.apache.commons.lang3.exception.ExceptionUtils;
import org.codehaus.jettison.json.JSONException;
import org.quartz.DisallowConcurrentExecution;
import org.quartz.Job;
import org.quartz.JobDataMap;
import org.quartz.JobExecutionContext;
import org.quartz.JobExecutionException;
import org.quartz.SchedulerException;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import net.sf.json.JSONArray;
import net.sf.json.JSONObject;


import com.bashpole.updentity.algorithms.metrics.Utilities;
import com.bashpole.updentity.dao.UserDao;
import com.bashpole.updentity.utility.EmailTemplates;
import com.bashpole.updentity.utility.UpdentityConstants;
import com.bashpole.updentity.utility.UpdentityUtil;

/**
 * 
 * @author Samuel Lawson, sjlawson@sdf.org
 * 
 */
@DisallowConcurrentExecution
public class OptinJob implements Job
    {
	private static Logger logger = LoggerFactory.getLogger(OptinJob.class);
    final String mapDelimiter = ":";
    private static String taskID;
    private static JSONArray sentLog;
    public OptinJob()
        {
        
        }
    
    /**
     * Throttled controller that executes php function via rest http request, 
     * sends list invitation emails to a given list. Executed by jsp job handler
     */
    @Override
    public void execute(JobExecutionContext context) throws JobExecutionException
        {
    	sentLog = new JSONArray();
    	JSONArray contactListSent;
    	String rawJSONArray;
        int limit = 100; 
        int offset = 0;
        int pageCounter = 1;
        int increment = limit;;
       
        JobDataMap dataMap = context.getJobDetail().getJobDataMap();
        taskID = dataMap.getString("taskID");
                
        String userID = dataMap.getString("userid");
        
        String contactListID = dataMap.getString("contactListID");
        
        String contactListLink = Constants.DATABASE_BASE_URL + "/" + Constants.DATABASE_CONTACT_LISTS + "/"
                + contactListID;
       
        try
            {
        	CouchUtilities.setTaskAsOpen(taskID);
        	// System.out.println("uid: " + userID + " clid: " + contactListID);
        	Thread.sleep(1000);
        	int contactCount = CouchUtilities.getContactCountForContactList(userID, contactListID);
        	
        	double totalPages = Math.ceil((double)contactCount / (double)limit);
            // Set the contact list status as running
        	JSONObject taskinfo = CouchUtilities.getTaskInfo(taskID);
        	do
            {
        	contactListSent = new JSONArray();
        	rawJSONArray = new String();
        	taskinfo.put("offset", offset);
        	taskinfo.put("limit", limit);
            // CouchUtilities.setContactListRunningStatus(true, contactListLink);
            
            // Set the task status as in progress in task.json
            
            String strTaskInfo = taskinfo.toString();
            // System.out.println("TaskINFO: " + strTaskInfo);
            String URL = UpdentityConstants.REST_URL + "?action=send_optin_emails&uid=" + userID + "&clid=" + contactListID; 
    		CouchConnect request = new CouchConnect(URL);
    		// EXECUTE BULK SEND
    		try {
    			Thread.sleep(250); //wait 1/4 of a sec for added stability
    			rawJSONArray = request.post(strTaskInfo);

    			/* debug code for dev
    			 * 
    			File file1 = new File("/usr/share/nginx/www/log/optin_java.log");
			    FileWriter writer1;
			    writer1 = new FileWriter(file1, true);
		        PrintWriter printer1 = new PrintWriter(writer1);
		        printer1.append("Pass "+pageCounter+"\n");
		        printer1.close();*/
    		} catch(Exception e)
	    		{
	    			try
	                {
	    				//localhost// File file = new File("/home/samuel/workspace/tmp/optin_java_exception.log");
	    				//Beta// File file = new File("/usr/share/nginx/www/log/optin_java_exception.log");
	    				File file = new File("/usr/share/nginx/log/optin_java_exception.log"); //enterprise
	    			    FileWriter writer;
	    			    writer = new FileWriter(file, true);
	    		        PrintWriter printer = new PrintWriter(writer);
	    		        printer.append(e.getMessage()+"\n StackTrace:\n" + e.getStackTrace()+"\n---------");
	    		        printer.close();
		                Thread.sleep(3000);
		                rawJSONArray = request.post(strTaskInfo);
		                contactListSent.add(rawJSONArray);
	                }
	    			catch(Exception e1)
	                {
	                logger.error(ExceptionUtils.getStackTrace(e1));
	                }	
	    		}
    		    		
    		offset += increment;
    		limit += increment;
            pageCounter += 1;
            contactListSent.add(rawJSONArray);
            
            sentLog.addAll(contactListSent.getJSONArray(0));
            
            }while(pageCounter <= totalPages);
        	//System.out.println("threads: " + (pageCounter - 1));
        	
        	taskinfo.put("log", sentLog);
        	saveSentLogToTask(taskinfo);
        	
            CouchUtilities.setTaskAsClosed(taskID);
            String UserInfoURL = UpdentityConstants.REST_URL + "?action=get_user&uid=" + userID;
            CouchConnect userRequest = new CouchConnect(UserInfoURL);
           
            JSONObject userData = JSONObject.fromObject( userRequest.get());
           
            // System.out.println(userData);
            String user_nicename = userData.getString("first_name") + " " + userData.getString("last_name");
            String title = "Opt-in messages sent";
            
            String txtMessage = "Hi, " + user_nicename + ",\n\n Your opt-in email task has finished running!\n\n";
                      
            String message = "<p>Hi "+ user_nicename + ",</p>\n";
            message += "<h3 style='color:#707070;margin:20px 0px 20px 0px'>Your opt-in email task has finished running!</h3>";
                       
            UserDao.sendEmailNotification(userID, "Your opt-in invites have been sent!", txtMessage, EmailTemplates.getStandardTemplate(title, message), UpdentityUtil.isProductionServer());
            }
        catch(MalformedURLException e)
            {
            try
                {
                CouchUtilities.setTaskAsFailed(taskID);
                }
            catch(IOException e1)
                {
                e1.printStackTrace();
                }
            catch(JSONException e1)
                {
                e1.printStackTrace();
                }
            e.printStackTrace();
            }
        catch(IOException e)
            {
            try
                {
                CouchUtilities.setTaskAsFailed(taskID);
                }
            catch(IOException e1)
                {
                e1.printStackTrace();
                }
            catch(JSONException e1)
                {
                e1.printStackTrace();
                }
            e.printStackTrace();
            }
        catch(Exception e)
            {
            try
                {
                CouchUtilities.setTaskAsFailed(taskID);
                }
            catch(IOException e1)
                {
                e1.printStackTrace();
                }
            catch(JSONException e1)
                {
                e1.printStackTrace();
                }
            e.printStackTrace();
            }
        finally
            {
            try
                {
                // Set the contact list status as not running
                CouchUtilities.removeTaskFromContactListQueue(taskID, contactListLink);
                
                // Suggest garbage collection
                Utilities.collectGarbage();
                
                // Get the next task in queue, if any
                taskID = SchedularUtilities.getNextTaskInQueue(contactListID);
                
                if(!taskID.equals(""))
                    {
                    SchedularUtilities schedulerUtils = new SchedularUtilities();
                    schedulerUtils.defineJob(taskID);
                    }
                }
            catch(IOException e)
                {
                e.printStackTrace();
                }
            catch(InterruptedException e)
                {
                e.printStackTrace();
                }
            catch(SchedulerException e)
                {
                e.printStackTrace();
                }
            catch(JSONException e)
                {
                e.printStackTrace();
                }
            }
        
        }

    
    /**
     * save current instance of taskinfo as task
     * @param taskinfo
     * @throws IOException
     * @throws JSONException
     */
    private static void saveSentLogToTask(JSONObject taskinfo)  throws IOException, JSONException {
    String taskID = taskinfo.getString("_id");  
    try
        {
        String taskLink = Constants.DATABASE_BASE_URL + "/" + Constants.DATABASE_TASKS + "/" + taskID;
        
        CouchConnect couchTasks = new CouchConnect(taskLink);
        
        System.out.println(couchTasks.put(taskinfo.toString()));
        }
    catch(HttpRetryException e)
        {
        if(e.responseCode() == 409)
            {
            try
                {
                Thread.sleep(10000);
                saveSentLogToTask(taskinfo);
                }
            catch(InterruptedException e1)
                {
                e1.printStackTrace();
                }
            }
        else
            {
            e.printStackTrace();
            }
        }
    }
    
    }
