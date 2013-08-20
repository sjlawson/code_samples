#!/bin/bash
# author: Samuel Lawson, samuel.lawson@bashpole.com
# recommended usage: cat inputfile | ./email_verifier.sh > output.csv
# inputfile can be comma or space-seperated: id, email@server.com 
# a simple email list can also be used, ids will be assigned, 
# sample output:
# 1, email@server.com, valid
#
# simple checking of individual emails will output a basic return value (not a csv list)
# ./email_verifier.sh email@server.com
# -> valid
#

checkEmail() {

#arguments: 1=email, 2=relay url, 3=skip in-depth email test 
# (enter "true" for argument 3 - lowercase without quotes - to skip the test, dramatically increasing speed at cost of less conclusive results)

email=$1
splode=(`echo $1 | tr '@' ' '`)
account=(${splode[0]})
domain=(${splode[1]})

# domain for the HELO statement - some servers require something, but usually don't check domain's validity
# arg 2 is probably optional, default is 'script.com', though you may get better results if this matches the 
# requesting server's URI

if [[ $# -lt 2 ]]
then
    relayUrl="ec2-50-19-87-60.compute-1.amazonaws.com"
else
    relayUrl=$2
fi

#echo Account: $account
#echo Domain: $domain
#echo Full address: $email
#echo 

# First check formatting something@something.something
regex="^[A-Za-z0-9!#\$%&'*+/=?^_\`{|}~-]+(\.[A-Za-z0-9!#$%&'*+/=?^_\`{|}~-]+)*@([A-Za-z0-9]([A-Za-z0-9-]*[A-Za-z0-9])?\.)+[A-Za-z0-9]([A-Za-z0-9-]*[A-Za-z0-9])?$"

if [[ $email =~ $regex ]] 
then
    validEmail=1 
#echo "Email address $email is valid."
else
    echo "invalidEmail"
    exit 0
fi

# now check for an MX record at the domain
# echo "MX check: "

nslook=`nslookup -type=mx $domain | grep -m 1 'mail exchanger'`

if [[ "$nslook" == *"mail exchanger"* ]]
then
   validMx=1 
#echo "Domain is valid"
else
    echo "invalidDomain"
    exit 0
fi


nslookArray=(`echo $nslook`) 
mxaddr=${nslookArray[${#nslookArray[@]}-1]}

# echo "mx address: $mxaddr"

mailfrom="MAIL FROM: <script@script.com>"
rcptto="RCPT TO: <$email>"


if [[ $3 == "true" ]]
then
    echo 'valid'
    exit 1
fi

#echo ${mxaddr%.}
#echo $relayUrl
#echo $mailfrom
#echo $rcptto

output=$(expect <<- DONE
#set timeout 2

spawn nc ${mxaddr%.} 25
expect "ESMTP"
send "HELO $relayUrl\r"
expect "service"
send "$mailfrom\r"
expect "OK"
send "$rcptto\r"
expect "OK"
send "quit\r"
DONE
);

# odd that this should return a Windows newline, but it does

cleanOut=`echo $output | tr '' ' '`;
outarray=( `echo ${cleanOut#*$rcptto}` )

#debugging:
#`echo $output > outputlog.txt`
#echo ${#outarray[@]}
#echo ${outarray[0]} 

if [[ $cleanOut == *"Recipient ok"* ]]
then
    echo "valid"
elif [[ $outarray[0] == *"250"* ]]
then
    echo "valid"
elif [[ $cleanOut == *"451"* ]]
then
    # this is the reply for 'greylisting' which incidates the existence of an account, non-existent account still return 550
    echo "valid"
elif [[ $cleanOut == *"553"* ]]
then 
    # 553 indicates some kind of anti-spam response, making this check impossible. Server responses include '550' in the string to fool spambots
    echo "inconclusive"
elif [[ $cleanOut == *"550"* ]]
then
    echo "invalid"
else
    echo "inconclusive"
fi
}


# if there is input from pipe, use the input formatted: 
# id email
# id email
# result=''
pipedinput=0

if [[ -n $1 ]]; then
  result=$(checkEmail $1 $2 $3)
  echo $result
else
count=0
while read line ; do
    let count++
    let pipedinput=1
    filteredline=(`echo "$line" | tr -d '\015'`)
    splode=(`echo "$filteredline" | tr ',' ' '`)
    id=${splode[0]}
    email=${splode[1]}
    if [[ -z $email ]]; then
	email=$id
	id=$count
    fi
    result=$(checkEmail $email)

    echo "$id, $email, $result"

done
fi

if [[ "$pipedinput" = 1 ]]; then
    #echo "EOF"
    exit
fi


# any inconclusive result should probably be treated as a valid email, therefore you could replace those return values with 'valid'
# the one sure thing is that a 550 response is a verified negative; can be sure the email is invalid

# manual:
#
# verify_email.sh
#
# Functionality: Given an email address, returns a value that can be used to discern whether the email is a real account. 
#
# System Requirements: 
# Linux/BSD/Unix, Bash (check first line for correct path), expect, nslookup, nc (netcat)
# For optimal functionality, the script should be run from a server with a static IP address because certain email servers like Hotmail and Yahoo will refuse connections from dynamic IP hosts.

# Installation: (just unpack the archive and run the script!)
# tar -xzf verify_email.tar.gz
# Check the path for the bash binary:
# which bash
# Alter line 1 to reflect the correct path
#    (e.g. #!/bin/bash)
#
# Usage:
# ./verify_email.sh email_address [relay_url] [true|false]
#
# System Requirements: 
# Linux/BSD/Unix, Bash (check first line for correct path), expect, nslookup, nc (netcat)
#
# email_address: full email address to be submitted for validation
#
# relayurl is optional, but ought to be a url that matches the server running the script. This is the url that is used for the ‘HELO’ command, some servers require a URL,
# (e.g. HELO example.com)
#
# Argument 3 (boolean): if true, skips the in-depth email account test. This could be useful if all that is required is to determine whether a given email address has a valid domain with an MX (mail exchange) record. If this returns true, there is a fair chance the email is valid. Setting this value to true skips the in-depth email account verification process (which takes several seconds per email).
#
# return values are:
# invalidEmail : indicates mal-formed email address
#
# invalidDomain: nslookup could not find an MX record for the domain
#
# valid: email should be considered valid - if Argument 3 is true, this is also the return for valid email exchange domain.
#
# invalid : got 550 response, email is definitely no-good
#
# inconclusive : got unexpected response from email server, possibly anti-spam settings prevent this kind of access. In most cases, an inconclusive response should be treated as a valid email until further notice. 
#
# If you get unexpected behaviour, uncomment line 89 to output the server response to a file called "outputlog.txt", be aware that, depending of the mail server, responses may include windows newlines (^M), so the unix 'cat' command might not show the whole file - use vi, emacs, or your favourite text editor to view this file.
#
# Proceedure:
# This script uses three methods to determine the validity of an email address.
# 1st test: Use a regular expression comparison to test whether it is a correctly formatted email.
# 2nd test: The script grabs the domain from the email, and tests (with nslookup) if the domain has a mail exchanger 
# 3rd test: Attempts to log-in to the mail exchanger smpt service port (25) and executes commands to test the existence of the email account. After sending “rcpt to: email”, if the server returns 250 or 451, the email is valid. If the server returns a 550 response, the email definitely does not exist. If it returns something else, then the result is “inconclusive” and it’s up to the user to decide whether the email is valid. 