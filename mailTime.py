# mailTime: Lightweight scheduled marketing mail solution

import datetime             # Check against schedule
import sqlite3              # Read/Write database

# Custom modules
import trayNotification     # Desktop notifications
import fExplorer            # Load emails
import gmail                # Send email

LIMIT = 100                 # Maximum no. of emails sent in a day
CLIENT_ID = 1               # ID of sender email address in database's client table

dbFile = "data\\mailTime_data.db"  # Main database
db = ""
c = ""

##### INITIALISE #####
def initialise():
    "Initialize database connection"
    global dbFile, c, db
    db = sqlite3.connect(dbFile) # Open database (create it if it does't exist)
    db.text_factory = str
    c = db.cursor()

def getClientData(user_id):
    "Read sender email from database"
    if c == "":
        initialise()

    c.execute("SELECT EMAIL FROM client WHERE USER_ID=?",(user_id,))
    data = c.fetchall()[0]

    client = {"email": data[0]}
    return client

##### SCHEDULING #####
def getSchedule():
    "Get all incomplete tasks in schedule"
    if c == "":
        initialise()

    c.execute("SELECT TASK_ID, TASK_NAME, START_DATE, START_TIME, STATUS FROM schedule WHERE STATUS!=?", ("COMPLETE",))
    tasks = c.fetchall()

    return tasks

def dateToday():
    "get today's date, formatted in the same way as the database field"
    return datetime.datetime.now(), datetime.datetime.now().strftime("%Y-%m-%d")

def dateHasPassed(datestring):
    "Returns True if datestring occured before today's date"
    today = dateToday()[0]
    shcedule = datetime.datetime.strptime(datestring, "%Y-%m-%d")
    if today > shcedule:
        return True
    else:
        return False

##### THROTTLE #####
def recordLastID(last_cust_id):
    """Record ID of last customer emailed today. If there are still more in the database,
    then set STATUS to 'IN_PROGRESS' otherwise, set it to 'COMPLETE'"""


# CONTINUE FROM HERE!



##### EMAILING #####
def getRecipients(task_id):
    "Fetch details of each recipient"
    if c == "":
        initialise()

    c.execute("SELECT STATUS, LAST_CUST_ID FROM tasks WHERE TASK_ID=?", (task_id,))
    taskDetail = c.fetchall()

    if taskDetail[0] == "IN_PROGRESS":
        last_cust_id = taskDetail[1]
    else:
        last_cust_id = 0
    
    c.execute("SELECT * FROM customers WHERE CUSTOMER_ID>? LIMIT ?", (last_cust_id, LIMIT))
    return c.fetchall()

def loadEmail(task_id):
    emailFile_HTML = fExplorer.listFiles("emails\{}".format(task_id), [".html"])[0]

    subject = emailFile_HTML.split("\\")[-1].split(".")[0] # Subject is the email file name

    emailFile_HTML = open(emailFile_HTML, "r")
    emailContent_HTML = emailFile_HTML.read()
    emailFile_HTML.close()

    try:
        emailFile_text = fExplorer.listFiles("emails\{}".format(task_id), [".txt"])[0]
        emailFile_text = open(emailFile_text, "r")
        emailContent_text = emailFile_text.read()
        emailFile_text.close()
    except:
        emailContent_text = "Please view this email in HTML format."

    return subject, emailContent_HTML, emailContent_text

def personaliseMail(html, text, recipient):
    "Add personal details from database into email template"
    
    # Dictionary of recipient data fields & their placeholder strings in the email:
    fields = {  "email": [recipient[1], "EMAIL"],
                "firstname": [recipient[2], "FIRSTNAME"],
                "surname": [recipient[3], "SURNAME"],
                "field1":[recipient[4], recipient[5]],
                "field2":[recipient[6], recipient[7]],
                "field3":[recipient[8], recipient[9]]
             }

    for key in fields:
        html = html.replace("^{}^".format(fields[key][1]), "{}".format(fields[key][0]))
        text = text.replace("^{}^".format(fields[key][1]), "{}".format(fields[key][0]))

    return html, text

def sendMail(task_id, recipients):
    "Send personalised email to each address in recipients"
    subject, html, text = loadEmail(task_id)

    for recipient in recipients:
        personal_html, personal_text = personaliseMail(html, text, recipient)
        message = gmail.CreateMultipartMessage(getClientData(CLIENT_ID)["email"], recipient[1], subject, personal_html, personal_text)
        #debugOutput(personal_html)

    # Record last recipient's ID in tasks table to continue tomorrow
    lastID = recipient[0]

##### MAIN #####
def main():
    tasks = getSchedule()

    for task in tasks:
        if dateHasPassed(task[2]):
            task_id = task[0]
            task_name = task[1]
            start_date = task[2]
            start_time = task[3]
            status = task[4]
            try:
                trayNotification.balloon_tip("mailTime: [#{}] {}".format(task_id, task_name), "This email task was scheduled to start on {} (yyyy/mm/dd) and is currently being processed.".format(start_date))
            except:
                # Only one balloon tip can be created by a program 
                pass

            recipients = getRecipients(task_id)
            sendMail(task_id, recipients)

##### DEBUG #####
def debugOutput(html):
    fileNum = len(fExplorer.listFiles("S:\\Python\\CMS\\test\\debug", [".html"])) + 1
    output = open("S:\\Python\\CMS\\test\\debug\\test_{}.html".format(fileNum), "w")
    output.write(html)
    output.close()

if __name__ == "__main__":
    main()
    


##### NOTES #####
# Task creation:
#   Record all fields in schedule
#   Insert ID and STATUS in tasks
#   Create subfolder based on task name

# Task execution:
#   Get STATUS from tasks
#   If STATUS=IN_PROGRESS, get LAST_CUST_ID from tasks (else LAST_CUST_ID = 0)
#   Select * from customers where ID > LAST_CUST_ID
#   Set STATUS=IN_PROGRESS in tasks

# Send mail:
#   Get email content from subfolder created in Task creation process
#   For first X customers...
#       Populate fields with data from customers query
#       Send
#       Record LAST_CUST_ID in tasks & set STATUS to IN_PROGRESS in both tasks and schedule
#   Pause until tomorrow, then start Task execution process again
#   When X > len(customers): set END_DATE in tasks STATUS=COMPLETE in tasks and schedule
