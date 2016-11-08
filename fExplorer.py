# Collection of mudeles useful fwhen working with windows explorer files & directores
import os
import zipfile

def listFiles(filepath, extensions):
    """List all files in specified folder with one of the listed extensions.
    filepath = string; full path to files
    extensions = list of extensions to include in search"""

    outputList = []
    fileList = os.listdir(filepath) # List all files in folder
    for name in fileList:
        # Loop through listed files
        for ext in extensions:
            if name.endswith(ext):
                outputList.append("{0}\\{1}".format(filepath, name))
    return outputList

def extractFile(msgZip, extractTo):
    zip_ref = zipfile.ZipFile(msgZip, "r")
    zip_ref.extractall(extractTo)
    zip_ref.close()


def monitor(path_to_watch,):
    "Monitor specified directory for file additions & deletions"
    before = dict ([(f, None) for f in os.listdir (path_to_watch)])
    while True:
        after = dict ([(f, None) for f in os.listdir (path_to_watch)])
        
        added = [f for f in after if not f in before]
        if added: 
            print "\n ".join(added)
        
        removed = [f for f in before if not f in after]
        if removed:
            print "\n ".join(removed)
            
        before = after
        time.sleep(emailDelay)
