from __future__ import print_function
import httplib2
import os

from apiclient import discovery, errors
import oauth2client
from oauth2client import client
from oauth2client import tools

from email.mime.text import MIMEText
from email.mime.multipart import MIMEMultipart
import base64

try:
   import argparse
   flags = argparse.ArgumentParser(parents=[tools.argparser]).parse_args()
except ImportError:
   flags = None

# If modifying these scopes, delete your previously saved credentials 
# at ~/.credentials/gmail-python-quickstart.json
SCOPES = "https://mail.google.com/"
CLIENT_SECRET_FILE = 'client_secret.json'
APPLICATION_NAME = 'Gmail API Python Quickstart'

def get_credentials():
    """Gets valid user credentials from storage.

    If nothing has been stored, or if the stored credentials are invalid,
    the OAuth2 flow is completed to obtain the new credentials.

    Returns:
    Credentials, the obtained credential."""
    home_dir = os.path.expanduser('~')
    credential_dir = os.path.join(home_dir, 'credentials')
    if not os.path.exists(credential_dir):
      os.makedirs(credential_dir)
    credential_path = os.path.join(credential_dir,
                                'gmail-python-quickstart.json')

    store = oauth2client.file.Storage(credential_path)
    credentials = store.get()
    if not credentials or credentials.invalid:
      flow = client.flow_from_clientsecrets(CLIENT_SECRET_FILE, SCOPES)
      flow.user_agent = APPLICATION_NAME
      if flags:
         credentials = tools.run_flow(flow, store, flags)
      else: # Needed only for compatibility with Python 2.6
         credentials = tools.run(flow, store)
      print('Storing credentials to ' + credential_path)
    return credentials

# create a message
def CreateMessage(sender, to, subject, message_text):
    """Create a message for an email.

    Args:
    sender: Email address of the sender.
    to: Email address of the receiver.
    subject: The subject of the email message.
    message_text: The text of the email message.

    Returns:
    An object containing a base64 encoded email object.
    """

    credentials = get_credentials()
    http = credentials.authorize(httplib2.Http())
    service = discovery.build('gmail', 'v1', http=http)
    
    message = MIMEText(message_text, 'html')
    message['to'] = to
    message['from'] = sender
    message['subject'] = subject

    rawMessage = {'raw': base64.b64encode(message.as_string())}
    SendMessage(service, "me", rawMessage)

def CreateMultipartMessage(sender, to, subject, message_html, message_text):

    credentials = get_credentials()
    http = credentials.authorize(httplib2.Http())
    service = discovery.build('gmail', 'v1', http=http)

    message = MIMEMultipart('alternative')
    message['Subject'] = subject
    message['From'] = sender
    message['To'] = to


    # Record the MIME types of both parts - text/plain and text/html.
    part1 = MIMEText(message_text, 'plain')
    part2 = MIMEText(message_html, 'html')

    # Attach parts into message container.
    # According to RFC 2046, the last part of a multipart message, in this case
    # the HTML message, is best and preferred.
    message.attach(part1)
    message.attach(part2)

    rawMessage = {'raw': base64.urlsafe_b64encode(message.as_string())}
    SendMessage(service, "me", rawMessage)

#send message 
def SendMessage(service, user_id, message):
    """Send an email message.

    Args:
     service: Authorized Gmail API service instance.
     user_id: User's email address. The special value "me"
     can be used to indicate the authenticated user.
     message: Message to be sent.

    Returns:
     Sent Message.
    """
    try:
        message = (service.users().messages().send(userId=user_id, body=message).execute())
        #print 'Message Id: %s' % message['id']
        return message
    except errors.HttpError, error:
        print ('An error occurred: %s' % error)


def main():
    """Shows basic usage of the Gmail API.
       Send a mail using gmail API
    """

    msg_body = "test message"

    text = "Hi!\nHow are you?\nHere is the link you wanted:\nhttp://www.python.org"
    html = """\
    <html>
      <head></head>
      <body>
        <p>Hi!<br>
           How are you?<br>
           Here is the <a href="http://www.python.org">link</a> you wanted.
        </p>
      </body>
    </html>
    """

    message = CreateMultipartMessage("AN_EMAIL@gmail.com", "AN_EMAIL@hotmail.co.uk", "Test HTML message", html, text)
    #message = CreateMessage("AN_EMAIL@gmail.com", "AN_EMAIL@hotmail.co.uk", "Test message", msg_body)

if __name__ == '__main__':
    main()
