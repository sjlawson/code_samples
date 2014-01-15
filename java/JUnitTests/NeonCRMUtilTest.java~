// Imports removed to protect proprietary code

/**
 * @author Samuel Lawson
 * 
 */
public class NeonCRMUtilTest
    {
    private static final String DEFAULT_MOCKDATA_PATH = "/home/samuel/workspace/updentity_rest_sjl/src/Test/java/com/bashpole/updentity/testutilities/resources/data";

    
    /**
     *  Test method for {@link NoenCRMUtil#addName(final JSONObject primaryContactObject, JSONObject convertedContact )}.
     * @throws IOException
     */
    @Test
    public final void testAddName() throws IOException
        {
        JSONObject resultantContact = new JSONObject(); // output object
        JSONObject neonContact = new JSONObject(); // updentity contact  | input1
       
        // Case 1: Missing fields - best behaviour is to return no change to contact
        JSONObject initContact = resultantContact;
        NeonCRMUtil.addName(neonContact, resultantContact);
        JSONAssert.assertEquals(initContact, resultantContact);

        String jsonFilename = DEFAULT_MOCKDATA_PATH + "/neon/name/importerinput.json";
        
        String jsonString = TestUtilities.readFileToString(jsonFilename);
        JSONObject testMainObject = JSONObject.fromObject(jsonString);
        
        Iterator<?> keys = testMainObject.keys();
        while( keys.hasNext() )
            {
            String key = (String)keys.next();
            if( testMainObject.get(key) instanceof JSONArray )
                {
                JSONArray testArray = testMainObject.getJSONArray(key);
                resultantContact = new JSONObject();

                //TODO: add TestUtilitites.removeDatesAndEncryptedFields(o) ;
                
                JSONObject externalContact = testArray.getJSONObject(0).getJSONObject("primaryContact");
                JSONObject expectedOutcome = testArray.getJSONObject(1);
               
                NeonCRMUtil.addName(externalContact, resultantContact);
              
System.out.println("At key:"+ key +" | Asserting, expected:\n"+expectedOutcome.toString()+"\nACTUAL:\n"+resultantContact.toString());
                JSONAssert.assertEquals(expectedOutcome, resultantContact);
                }
            }
        }
    
    /**
     * Test method for {@link NoenCRMUtil#addPhones(final JSONObject primaryContactObject, JSONObject convertedContact )}.
     * @throws IOException
     */
    @Test
    public final void testAddPhones() throws IOException
        {
        JSONObject resultantContact = new JSONObject(); // output object
        JSONObject neonContact = new JSONObject(); // updentity contact  | input1
       
        // Case 1: Missing fields - best behaviour is to return no change to contact
        JSONObject initContact = resultantContact;
        NeonCRMUtil.addPhones(neonContact, resultantContact);
        JSONAssert.assertEquals(initContact, resultantContact);

        String jsonFilename = DEFAULT_MOCKDATA_PATH + "/neon/phone/importerinput.json";
        
        String jsonString = TestUtilities.readFileToString(jsonFilename);
        JSONObject testMainObject = JSONObject.fromObject(jsonString);
        
        Iterator<?> keys = testMainObject.keys();
        while( keys.hasNext() )
            {
            String key = (String)keys.next();
            if( testMainObject.get(key) instanceof JSONArray )
                {
                JSONArray testArray = testMainObject.getJSONArray(key);
                resultantContact = new JSONObject();

                //TODO: add TestUtilitites.removeDatesAndEncryptedFields(o) ;
                
                JSONObject externalContact = testArray.getJSONObject(0).getJSONObject("primaryContact");
                JSONObject expectedOutcome = testArray.getJSONObject(1);
               
                NeonCRMUtil.addPhones(externalContact, resultantContact);
              
System.out.println("At key:"+ key +" | Asserting, expected:\n"+expectedOutcome.toString()+"\nACTUAL:\n"+resultantContact.toString());
                JSONAssert.assertEquals(expectedOutcome, resultantContact);
                }
            }
        }
    
    /**
     * Test method for {@link NoenCRMUtil#addEmails(final JSONObject primaryContactObject, JSONObject convertedContact )}.
     * @throws IOException 
     */
    @Test
    public final void testAddEmails() throws IOException
        {
        JSONObject resultantContact = new JSONObject(); // output object
        JSONObject neonContact = new JSONObject(); // updentity contact  | input1
       
        // Case 1: Missing fields - best behaviour is to return no change to contact
        JSONObject initContact = resultantContact;
        NeonCRMUtil.addEmails(neonContact, resultantContact);
        JSONAssert.assertEquals(initContact, resultantContact);

        String jsonFilename = DEFAULT_MOCKDATA_PATH + "/neon/email/importerinput.json";
        
        String jsonString = TestUtilities.readFileToString(jsonFilename);
        JSONObject testMainObject = JSONObject.fromObject(jsonString);
        
        Iterator<String> keys = testMainObject.keys();
        
        // for(String key : keys)
        while( keys.hasNext() )
            {
            String key = (String)keys.next();
            if( testMainObject.get(key) instanceof JSONArray )
                {
                JSONArray testArray = testMainObject.getJSONArray(key);
                resultantContact = new JSONObject();

                //TODO: add TestUtilitites.removeDatesAndEncryptedFields(o) ;
                
                JSONObject externalContact = testArray.getJSONObject(0).getJSONObject("primaryContact");
                JSONObject expectedOutcome = testArray.getJSONObject(1);
               
                NeonCRMUtil.addEmails(externalContact, resultantContact);
              
System.out.println("At key:"+ key +" | Asserting, expected:\n"+expectedOutcome.toString()+"\nACTUAL:\n"+resultantContact.toString());
                JSONAssert.assertEquals(expectedOutcome, resultantContact);
                }
            }
        }
    

    
    }
