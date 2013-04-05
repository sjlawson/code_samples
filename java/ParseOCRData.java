package my.datagrabber;

import java.util.ArrayList;
import java.util.List;
import java.util.regex.Matcher;
import java.util.regex.Pattern;

// Imports for debugging
import javax.swing.JDialog;
import javax.swing.JLabel;
import javax.swing.JOptionPane;
import javax.swing.JPanel;
import java.awt.BorderLayout;

/**
 *
 * @author samuel lawson, sjlawson@sdf.org
 */
public class ParseOCRData {
    String Address1 = "(\\d+\\s+[':.,\\s\\w#]{0,200},*\\s*[A-Za-z]{0,2}\\s*\\d{5}(-\\d{4})?)";
    String Address2 = "(.*)\\s*(.*)\\s*(.*)\\s(\\w+)\\,+\\s(\\w{2,3})\\s(\\d{5}(\\-\\d{4})*)\\s*(\\w+)*";
    String Address3 = "suite|apt|door|room|suite|apartment|street|ave|road|way|st|rd|blvd|circle|pkwy|avenue";

    String Zip1 = "\\d{5}(-\\d{4})?";

//State,City names can be included from a file. 

// Better option strip all the non digit characters than check the length
    String Phone1 = "(?:(?:\\+?1\\s*(?:[.-]\\s*)?)?(?:\\(\\s*([2-9]1[02-9]|[2-9][02-8]1|[2-9][02-8][02-9])\\s*\\)|([2-9]1[02-9]|[2-9][02-8]1|[2-9][02-8][02-9]))\\s*(?:[.-]\\s*)?)?([2-9]1[02-9]|[2-9][02-9]1|[2-9][02-9]{2})\\s*(?:[.-]\\s*)?([0-9]{4})(?:\\s*(?:#|x\\.?|ext\\.?|extension)\\s*(\\d+))?";
    String Phone2 = "(\\d{3})-(\\d{3})-(\\d{4})";
    //String Phone3 = "([\\+][0-9]{1,3}([ \\.\\-])?)?([\\(]{1}[0-9]{3}[\\)])?([0-9A-Z \\.\\-]{1,32})((x|ext|extension)?[0-9]{1,4}?)";
    String Phone4 = "(((\\(?\\d{3}\\)?|\\d{3})( |-|\\.))|(\\(?\\d{3}\\)?|\\d{3}))?\\d{3}( |-|\\.)?\\d{4}(( |-|\\.)?([Ee]xt|[Xx])[.]?( |-|\\.)?\\d{4})?";
//Pattern for web crawler to find contact details
    String Phone5 = "[\\+]{0,1}(\\d{10,13}|[\\(][\\+]{0,1}\\d{2,}[\\13)]*\\d{5,13}|\\d{2,6}[\\-]{1}\\d{2,13}[\\-]*\\d{3,13})";
    String PhoneAllInclusive = "\\+?[loO]?\\d?\\(?[\\d\\s?loO\\)?\\.?\\-?\\,?ext?extension?:?_?]*\\d|[loO]";
//Email
    String Email1 = "[_A-Za-z0-9-]+(\\.[_A-Za-z0-9-]+)*@[A-Za-z0-9]+(\\.[A-Za-z0-9]+)*(\\.[A-Za-z]{2,})";
    //String Email2 = "[a-z0-9!#$%&'*+\\/=?^_`{|}~-]+(?:\\.[a-z0-9!#$%&'*+\\/=?^_`{|}~-\\+]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\\.)+(?:[A-Z]{2}|com|org|net|edu|gov|mil|biz|info|mobi|name|aero|asia|jobs|museum)";
    String Email2 = "[\\w\\d!#\\$%&'\\*+\\/=\\?\\^_`{|}~-]+([\\+|\\.][\\w\\d\\s!#\\$%&'\\*+\\/=\\?\\^_`{|}~-]+)*\\s?@\\s?(?:[\\w\\d\\s](?:[\\w\\d\\s-]*[\\w\\d\\s])?\\.)+(?:[\\w]{2,6}|com|org|net|edu|gov|mil|biz|info|mobi|name|aero|asia|jobs|museum|tv|uk)";
    String Email3 = "[A-Z0-9._%+-]+@[\\w\\d\\s\\.-]+\\.[\\w]{2,8}";
    String Email4 = "\\S+@\\S+\\.\\S+$"; 
    String Name1 = "\\D+[A-Z][a-zA-Z]+";
    
    // clean-up patterns
    String LIoto1 = "([\\d])([lIi\\|oO]+)([\\d])";
    String nums01toL = "([\\w])([01]+)([\\w])";
    
    // no need to be strict - we want to find all possibilities of email addresses
    public List<String[]> ParseOCRData(String ocrData) {
	String interpreted = "<html>";
	String current = null;
        
	ocrData = ocrData.replaceAll("I-I", "H");
	String currentReplaced = "";
	
		
	Pattern pNums01toL = Pattern.compile(nums01toL);
	Matcher mNums01toLO = pNums01toL.matcher(ocrData);
	while(mNums01toLO.find()) {
	    current = mNums01toLO.group();
	    currentReplaced = current.replaceAll("1", "l");
	    //currentReplaced = currentReplaced.replaceAll("0", "o"); // zero to 'o'
	    ocrData = ocrData.replaceAll(current, currentReplaced);
	}
	
	Pattern pLIoto1 = Pattern.compile(LIoto1);
	Matcher mLIoto1 = pLIoto1.matcher(ocrData);
	while(mLIoto1.find()) {
	    current = mLIoto1.group();
	    
	    currentReplaced = current.replaceAll("l|i|I|\\|", "1");
	    currentReplaced = currentReplaced.replaceAll("o|O", "0");
		    
	    ocrData = ocrData.replaceAll(current, currentReplaced);
	    
	}
	//String fixLs = ocrData.replaceAll("([\\d])([l]+)([\\d])", "$2");
	//ocrData = ocrData.replaceAll("^([0-9])([l]+)([0-9])$", "$1" + fixLs.replace('l', '1') + "$3");
	
	ocrData = ocrData.replaceAll("([a-z])([0])([a-z])", "$1" + "o" + "$3");
	ocrData = ocrData.replaceAll("(\\s)([0])([a-z])", "$1" + "o" + "$3");
	ocrData = ocrData.replaceAll("([a-z])([0])(\\s)", "$1" + "o" + "$3");
	ocrData = ocrData.replaceAll("([A-Z])([0])([A-Z])", "$1" + "O" + "$3");
	ocrData = ocrData.replaceAll("(\\s)([0])([A-Z])", "$1" + "O" + "$3");
	ocrData = ocrData.replaceAll("([A-Z])([0])(\\s)", "$1" + "O" + "$3");
	
	ocrData = ocrData.replaceAll("([a-z])([1])([a-z])", "$1" + "l" + "$3");
	ocrData = ocrData.replaceAll("([\\s\\.])([1])([a-z])", "$1" + "l" + "$3");
	ocrData = ocrData.replaceAll("([a-z])([1])([\\s\\.])", "$1" + "l" + "$3");
	ocrData = ocrData.replaceAll("([A-Z])([1])([A-Z])", "$1" + "I" + "$3");
	ocrData = ocrData.replaceAll("(\\s)([1])([A-Z])", "$1" + "I" + "$3");
	ocrData = ocrData.replaceAll("([A-Z])([1])(\\s)", "$1" + "I" + "$3");
	
	ocrData = ocrData.replaceAll("([a-z\\.])([\\|])([a-z\\.])", "$1" + "l" + "$3");
	ocrData = ocrData.replaceAll("([^\\.]\\s)([\\|])([a-z\\.])", "$1" + "l" + "$3");
	ocrData = ocrData.replaceAll("([a-z\\.])([\\|])(\\s)", "$1" + "l" + "$3");
	
	ocrData = ocrData.replaceAll("(\\s)([\\|])(\\s)", "$1" + "I" + "$3");
	ocrData = ocrData.replaceAll("([A-Z])([\\|])([A-Z])", "$1" + "I" + "$3");
	ocrData = ocrData.replaceAll("([\\s\\.])([\\|])([A-Z])", "$1" + "I" + "$3");
	ocrData = ocrData.replaceAll("([A-Z])([\\|])([\\.\\s])", "$1" + "I" + "$3");
	
	ocrData = ocrData.replaceAll("([\\W\\s\\d])([l])([\\W\\s])", "$1"+"1"+"$3");
	ocrData = ocrData.replaceAll("([\\W\\s])([l])([\\W\\s\\d])", "$1"+"1"+"$3");
	ocrData = ocrData.replaceAll("([\\W\\d])([o|O])([\\W\\d])", "$1"+"0"+"$3");
	
	System.out.println(ocrData);
	/* test string
	 * 
	 samue|.1aws0n @ bash p0le.c0m
	 (3l7 ) 2o7 -2ll2 ext. l23
	 
	 * 
	 */
	
	List<String[]> contactData = new ArrayList<String[]>();
	
	ArrayList<String> emails = new ArrayList<String>();
	ArrayList<String> phones = new ArrayList<String>();
	ArrayList<String> addresses = new ArrayList<String>();
	ArrayList<String> zips = new ArrayList<String>();
	ArrayList<String> names = new ArrayList<String>();
	
	current = null;
	int totalCount = 0;
	int addressPosition = 0;
	
	Pattern pEmail2 = Pattern.compile(Email2);
	Matcher mEmail2 = pEmail2.matcher(ocrData);
	while(mEmail2.find()) {
	    current = mEmail2.group();
	    current = current.replaceAll("\\s", "");
	    interpreted += "<br>Email2: " + current;
	    
	    if( emails.isEmpty() || !emails.contains(current))
	    {
	    emails.add(current);
	   
	    // contactData[totalCount] = new String[] { "Email", current };
	    contactData.add(new String[] { "email", current });
		totalCount++;
	    }
	    ocrData = ocrData.replace(current, "");
	}
	
	Pattern pZip1 = Pattern.compile(Zip1);
	Matcher mZip1 = pZip1.matcher(ocrData);
	while(mZip1.find()) {
	    //contactData[rowCount] = new String[] {"Address", mAddress3.group() };
	    
	    current = mZip1.group();
	    interpreted += "<br>Zip1: " + current;
	    
	    if(zips.isEmpty() || (!zips.contains(current) && !addresses.contains(current)) )
	    {
		zips.add(current);
		contactData.add(new String[] { "zip", current });
		totalCount++;
	    }
	    ocrData = ocrData.replace(current, "");
	}
	
	Pattern pPhone1 = Pattern.compile(Phone1);
	Pattern pPhone2 = Pattern.compile(Phone2);
	Pattern pPhone4 = Pattern.compile(Phone4);
	
	
	Pattern pPhoneA = Pattern.compile(PhoneAllInclusive);
	Matcher mPhoneA = pPhoneA.matcher(ocrData);
	
	//String pCurrent = "";
	while(mPhoneA.find()) {
	    current = mPhoneA.group();
	    current = current.replaceAll("\\s([\\d\\W])", "$1"); //strip all whitespace unless it preceeds a number or non-word character
	    current = current.replaceAll("l", "1");
	    current = current.replaceAll("o|O", "0");
	    current = current.replaceAll("extensi0n", "extension");
	    interpreted += "<br>PhoneA: " + current;
	    //check against more restrictive patterns
	    Matcher mPhoneB = pPhone1.matcher(current);
	    Matcher mPhoneC = pPhone2.matcher(current);
	    Matcher mPhoneD = pPhone4.matcher(current);
	    
	    if((phones.isEmpty() || !phones.contains(current)) && (mPhoneB.find() || mPhoneC.find() || mPhoneD.find()))
	    {
		phones.add(current);
		//contactData[totalCount] = new String[] {"Phone", current };
		contactData.add(new String[] { "phone", current });
		totalCount++;
	    }
	    ocrData = ocrData.replace(current, "");
	}
	/*
	
	Matcher mPhone1 = pPhone1.matcher(ocrData);
	 
	while(mPhone1.find()) {
	    current = mPhone1.group();
	    interpreted += "<br>Phone1: " + current;
	    if(phones.isEmpty() || !phones.contains(current))
	    {
		phones.add(current);
		//contactData[totalCount] = new String[] {"Phone", current };
		contactData.add(new String[] { "phone", current });
		totalCount++;
	    }
	
	}
	
	Matcher mPhone2 = pPhone2.matcher(ocrData);
	
	while(mPhone2.find()) {
	    current = mPhone2.group();
	    interpreted += "<br>Phone2: " + current;
	    if(phones.isEmpty() || !phones.contains(current))
	    {
		phones.add(current);
		//contactData[totalCount] = new String[] {"Phone", current };
		contactData.add(new String[] { "phone", current });
		totalCount++;
	    }
	
	}
	 * */
	 
	/*
	
//	
//	Matcher mPhone3 = pPhone3.matcher(ocrData);
	
	while(mPhone3.find()) {
	    current = mPhone3.group();
	    interpreted += "<br>Phone3: " + current;
	    if(phones.isEmpty() || !phones.contains(current))
	    {
		phones.add(current);
		//contactData[totalCount] = new String[] {"Phone", current };
		contactData.add(new String[] { "phone", current });
		totalCount++;
	    }
	
	}
	 
	
	Pattern pPhone4 = Pattern.compile(Phone4);
	Matcher mPhone4 = pPhone4.matcher(ocrData);
	while(mPhone4.find()) {
	    current = mPhone4.group();
	    interpreted += "<br>Phone4: " + current;
	    if(phones.isEmpty() || !phones.contains(current))
	    {
		phones.add(current);
		//contactData[totalCount] = new String[] {"Phone", current };
		contactData.add(new String[] { "phone", current });
		totalCount++;
	    }
	
	}
	
	Pattern pPhone5 = Pattern.compile(Phone5);
	Matcher mPhone5 = pPhone5.matcher(ocrData);
	while(mPhone5.find()) {
	    current = mPhone5.group();
	    interpreted += "<br>Phone5: " + current;
	    if(phones.isEmpty() || !phones.contains(current))
	    {
		phones.add(current);
		//contactData[totalCount] = new String[] {"Phone", current };
		contactData.add(new String[] { "phone", current });
		totalCount++;
	    }
	
	}
*/	
	Pattern pAddress1 = Pattern.compile(Address1);
	Matcher mAddress1 = pAddress1.matcher(ocrData);
	
	int position = 0;
	StringBuilder phrase = new StringBuilder(ocrData);
	while(mAddress1.find()) {
	    current = mAddress1.group();
	    
	    //one method of finding a name is to regex string before address
	    position = phrase.indexOf(current);
	    if(position > 0 && (position < addressPosition || addressPosition == 0 )) {
		addressPosition = position;
	    }
		    
	    interpreted += "<br>Address1: " + current;
	    
	    if(addresses.isEmpty() || !addresses.contains(current))
	    {
		current = current.replaceAll(Zip1, "");
		addresses.add(current);
		//contactData[totalCount] = new String[] {"Address", current };
		contactData.add(new String[] { "address", current });
		totalCount++;
	    }
	    ocrData =  ocrData.replaceAll(current, "");
	}
	/*
	Pattern pAddress2 = Pattern.compile(Address2);
	Matcher mAddress2 = pAddress2.matcher(ocrData);
	while(mAddress2.find()) {
	    current = mAddress2.group();
	    interpreted += "<br>Address2: " + current;
	    
	    position = phrase.indexOf(current);
	    if(position > 0 && (position < addressPosition || addressPosition == 0 )) {
		addressPosition = position;
	    }
	    
	    if(addresses.isEmpty() || !addresses.contains(current))
	    {
		addresses.add(current);
		
		contactData.add(new String[] { "address", current });
		totalCount++;
	    }
	}
	*/
	
	Pattern pAddress3 = Pattern.compile(Address3);
	Matcher mAddress3 = pAddress3.matcher(ocrData);
	while(mAddress3.find()) {
	    //contactData[rowCount] = new String[] {"Address", mAddress3.group() };
	    
	    current = mAddress3.group();
	    interpreted += "<br>Address3: " + current;
	    
	    position = phrase.indexOf(current);
	    if(position > 0 && (position < addressPosition || addressPosition == 0 )) {
		addressPosition = position;
	    }
	    
	    if(addresses.isEmpty() || (!addresses.contains(current) ))
	    {
		addresses.add(current);
		contactData.add(new String[] { "address", current });
		totalCount++;
	    }
	    ocrData = ocrData.replaceAll(current, "");
	}
	
	if(addressPosition > 0) {
	ocrData = ocrData.substring(0, addressPosition);
	}
	
	Pattern pName1 = Pattern.compile(Name1);
	Matcher mName1 = pName1.matcher(ocrData);
	while(mName1.find()) {
	    //contactData[rowCount] = new String[] {"Address", mAddress3.group() };
	    
	    current = mName1.group();
	    interpreted += "<br>Name1: " + current;
	    
	    if(!names.contains(current) && !addresses.contains(current) )
	    {
		names.add(current);
		contactData.add(new String[] { "name", current });
		totalCount++;
	    }
	}
// Debugging panel
//	

//	JPanel testout = new JPanel(new BorderLayout());
//	JLabel testLabel = new JLabel(interpreted);
//
//	testout.add(testLabel, BorderLayout.CENTER);
//	System.out.println(interpreted);
//	JOptionPane op = new JOptionPane(testout, JOptionPane.DEFAULT_OPTION );
//        JDialog dlg = op.createDialog("Hello World!");
//        dlg.setVisible(true);
	
	return contactData;
    }
    
}
