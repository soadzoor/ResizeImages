Useful: to rename all the files in a folder, you can use the following powershell command:

dir | rename-item -NewName {$_.name -replace ".jpg", "_original.jpg"}