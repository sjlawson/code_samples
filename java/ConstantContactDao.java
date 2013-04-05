package com.bashpole.updentity.dao;

import java.io.IOException;
import java.net.HttpRetryException;

import net.sf.json.JSONArray;
import net.sf.json.JSONObject;

import com.bashpole.updentity.utility.UpdentityConstants;
import com.bashpole.updentity.webresources.CouchConnect;

/*
 * Data access object for Constant Contact 
 */
public class ConstantContactDao {
    private String username;
    private String ctctListId;
    private String password;
    
    public ConstantContactDao(String username, String password, String ctctListId) 
    {
    this.username = username;
    this.password = password;
    this.ctctListId = ctctListId;
    }

/**
 * Returns true if the username and password are valid on Constant Contact.
 * @throws IOException 
 * 
 * I think there are better ways to do this: I.E. write a java library for interfacing with CC
 * 
 */
public static boolean isAuthentic(String username, String password) throws IOException
    {
    String URL = UpdentityConstants.REST_URL + "?action=authenticate_constantContact&uname=" + username
            + "&pname=" + password;
    try {
    CouchConnect request = new CouchConnect(URL);
    JSONArray ccResult = JSONArray.fromObject(request.get());
    if(ccResult.size() != 0) 
        return true;
    else
        return false;
    } catch(HttpRetryException e) {
        return false;
    }

    }
    
    /**
     * 
     * @param limit
     * @param offset
     * @return
     * @throws HttpRetryException
     * @throws IOException
     * 
     * http://[host]/updentity_rest_php/updentity-rest/rest.php?action=constantContact_list_contacts&limit=1&offset=1&uname=[username]&pname=[password]
     */
    public JSONObject listContacts(int limit, int offset) throws HttpRetryException, IOException
    {
        
                String URL = UpdentityConstants.REST_URL + "?action=constantContact_list_contacts&" +
                        "limit=" + limit + "&offset=" + offset +
                        "&listid=" + this.ctctListId +
                        "&uname=" + this.username
                        + "&pname=" + this.password;
                CouchConnect request = new CouchConnect(URL);
                // System.out.println("TheRequest: " + request);
                JSONObject contacts = JSONObject.fromObject(request.get());
                
           
        return contacts;
    }
    
    /**
     * 
     * @return count
     * @throws HttpRetryException
     * @throws IOException
     */
    public int getContactCount() throws HttpRetryException, IOException
    {
        int count;
        String URL = UpdentityConstants.REST_URL + "?action=constantContact_count_contacts&uname=" + this.username
                + "&pname=" + this.password + "&listid=" + this.ctctListId;
        CouchConnect request = new CouchConnect(URL);
        // System.out.println("\n User: " + this.username);
        
        JSONObject subscriberCountObj = JSONObject.fromObject(request.get());
        
        //System.out.println("\n CountObj: " + subscriberCountObj.toString());
        count = subscriberCountObj.getInt("subscibers");
        //System.out.println("\ncontact count: " + count);
        
        return count;
    }
    
    
    
}
