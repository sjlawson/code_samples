//imports removed to protect proprietary code

/**
 * 
 * @author Samuel Lawson
 * These tests use a JSON document to compare input and actual output against expected output defined
 */
public class BatchbookContactsExporterTest
    {
    private static final String DEFAULT_MOCKDATA_PATH = "/[pathRemovedForSecurity]/testutilities/resources/data";
    private static final String ORGNAME = "testorgname";
    private static final String APIKEY =  "testapikey";
    private static final String ENDPOINTID = "testepid";
    
    
    /**
     * 
     * @throws IOException
     */
    @Test
    public void testAddEmails() throws IOException
        {
        BatchbookContactsExporter exporter = new BatchbookContactsExporter(ORGNAME, APIKEY, ENDPOINTID);
        JSONObject bbContact = new JSONObject(); // output object
        JSONObject contact = new JSONObject(); // updentity contact  | input1
        JSONObject currentBBContact = new JSONObject(); // current data from batchbook | input2
        
        // Case 1: Missing fields - best behaviour is to return no change to contact
        JSONObject beforeBBContact = bbContact;
        exporter.addEmails(bbContact, contact, currentBBContact);
        JSONAssert.assertEquals(beforeBBContact, bbContact);

        String jsonFilename = DEFAULT_MOCKDATA_PATH + "/email/exporterinput.json";
        
        String jsonString = TestUtilities.readFileToString(jsonFilename);
        JSONObject testMainObject = JSONObject.fromObject(jsonString);
        
        Iterator<?> keys = testMainObject.keys();
        while( keys.hasNext() )
            {
            String key = (String)keys.next();
            if( testMainObject.get(key) instanceof JSONArray )
                {
                JSONArray testArray = testMainObject.getJSONArray(key);
                JSONObject bbContactResult = new JSONObject();

                JSONObject outgoingContact = testArray.getJSONObject(0);
                JSONObject externalContact = testArray.getJSONObject(1);
                JSONObject expectedOutcome = testArray.getJSONObject(2);
                for(int e=0; e < outgoingContact.getJSONArray(ContactKeys.EMAILS).size(); e++)
                    {
                    exporter.addEmails(bbContactResult, outgoingContact.getJSONArray(ContactKeys.EMAILS).getJSONObject(e), externalContact);
                    }
System.out.println("At key:"+ key +" | Asserting, expected:\n"+expectedOutcome.toString()+"\nACTUAL:\n"+bbContactResult.toString());
                JSONAssert.assertEquals(expectedOutcome, bbContactResult);
                }
            }
        }
    
    
    /**
     * 
     * @throws IOException
     */
    @Test
    public void testAddNotes() throws IOException
        {
        BatchbookContactsExporter exporter = new BatchbookContactsExporter(ORGNAME, APIKEY, ENDPOINTID);
        JSONObject bbContact = new JSONObject(); // output object
        JSONObject contact = new JSONObject(); // updentity contact  | input1
        JSONObject currentBBContact = new JSONObject(); // current data from batchbook | input2
        
        // Case 1: Missing fields - best behaviour is to return no change to contact
        JSONObject beforeBBContact = bbContact;
        exporter.addNotes(bbContact, contact, currentBBContact);
        JSONAssert.assertEquals(beforeBBContact, bbContact);

        String jsonFilename = DEFAULT_MOCKDATA_PATH + "/notes/input.json";
        String jsonString = TestUtilities.readFileToString(jsonFilename);
        JSONArray testArray = JSONArray.fromObject(jsonString);
        
        for(int i=0; i < testArray.size(); i++)
            {
            JSONObject bbContactResult = new JSONObject();

            JSONObject outgoingContact = testArray.getJSONArray(i).getJSONObject(0);
            JSONObject externalContact = testArray.getJSONArray(i).getJSONObject(1);
            JSONObject expectedOutcome = testArray.getJSONArray(i).getJSONObject(2);
            RuntimeException ex=null;
            try {
                if(!JSONUtils.isString(outgoingContact.get(ContactKeys.NOTES)))
                    {
                    throw new RuntimeException("Contact notes is not String: " + outgoingContact.get(ContactKeys.NOTES));
                    }
                if(!JSONUtils.isString(externalContact.get("about")) && !JSONUtils.isNull(externalContact.get("about")) )
                    {
                    throw new RuntimeException("Batchbook contact 'about' field is not String:"+externalContact.get("about"));
                    }
                exporter.addNotes(bbContactResult, outgoingContact, externalContact);
            } catch (RuntimeException e)
                {
                ex=e;
//System.out.println(e.getMessage());
                }
            if(expectedOutcome.has("updentity_exception"))
                {
                if(ex == null) 
                    {
                    JSONAssert.fail("Expected exception not thrown: " + expectedOutcome.has("updentity_exception"));
                    } 
                else
                    {
                    if(!ex.getClass().getName().equals(expectedOutcome.getString("updentity_exception")))
                        {
                        JSONAssert.fail("Unexpected exception - Expected:" + expectedOutcome.getString("updentity_exception") + " Got:" + ex.getClass().getName());
                        }
                    }
                }
            else 
                {
//System.out.println("Line "+ (i+2) +". Asserting, expected:"+expectedOutcome.toString()+"|actual:"+bbContactResult.toString());
                JSONAssert.assertEquals(expectedOutcome, bbContactResult);
                }
            } 
        }
    
 
    /**
     * This results in a null pointer exception b/c the test doesn't access the API
     * Started creating a method to check if the API is accessible
     * 
     * @throws IOException
     */
    @Ignore
    @Test
    public void testConvertJSONContactToBatchbook() throws IOException
        {
        BatchbookContactsExporter exporter = new BatchbookContactsExporter(ORGNAME, APIKEY, ENDPOINTID);
        MockDataGenerator inputContactGenerator = new MockDataGenerator("data.csv");
        JSONObject updentityContact = inputContactGenerator.getContactDataFromJSONSchema("updentity_contact.json");
System.out.println("updentityContact: "+updentityContact.toString());
        
        JSONObject batchbookContact = exporter.convertJSONContactToBatchbook(updentityContact);
System.out.println("batchbookContact: "+batchbookContact.toString());
        
        }
    
    }
