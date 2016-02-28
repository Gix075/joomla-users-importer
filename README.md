# joomla3 Users Importer (version 2.0.2)
Import users from CSV file into Joomla 3 database

I've developed this tool for a massive users upload into some different joomla3 websites.<br>
I hope it can be useful for you too.<br>

## overview
This tool import users from CSV/TXT file into Joomla3 database.<br>
This tool require a file formatted as:
```
Name1,Surname1,email1@email.xx
Name2,Surname2,email2@email.xx
Name3,Surname3,email3@email.xx
...
...
```
Joomla Users Importer will generate an **username** and a **password** for each user.

### username and password
Each **username** will be generated unifying lowercase *"Name"* and *"Surname"* CSV columns, using an underscore *"_"*<br>
**name1_surname1**

Each **password** will be generated unifying username with a prefix *"password_"*<br>
**password_name1_surname1**

## usage

It's very easy and intuitive. 

* Just upload tool by FTP on your server (where is running your joomla db)
* Upload your CSV file inside `joomla-users-importer/csv` directory
* Launch tool using a browser: `http://yourdomain.xxx/path/to/joomla-users-importer/`
* Fill all form fields with your own data

*You can find some documentation on tool form page*

## logs
After each import Joomla Users Importer will save a detailed logfile with a list of all added users. Log files will be saved inside `joomla-users-importer/logs` directory
