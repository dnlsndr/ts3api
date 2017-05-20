# ts3api
A NODE server that connects to your local Ts3 instance for easy communication via api with php or other languages.


Please create a config.json file in the root folder. 

Fill out the following example and copy it into the config.json 

config.json example:
```json
{
    "server":{
        "loginname": "< query login name >",
        "loginpw" : "< login password for your query >",  
        "apiport" : "< the port, your api is available at localhost >",
        "socport" : "< socket port for the ts3 query login, default is: 10011 >",
        "sochost" : "< socket host, normally its 127.0.0.1 >",
        "apiaccess" : "< access type to the api, if set to 'local' access is granted to localhost only >", 
        "nickname" : "< name of the bot >",
        "sid" : "< virtual ts3 server id, normally its 1 >"
    },
    
    "local":{
        "loginname": "< query login name >", 
        "loginpw" : "< login password for your query >", 
        "apiport" : "< the port, your api is available at localhost >",
        "socport" : "< socket port for the ts3 query login, default is: 10011 >",
        "sochost" : "< socket host, normally its 127.0.0.1 >",
        "apiaccess" : "< access type to the api, if set to 'local' access is granted to localhost only >", 
        "nickname" : "< name of the bot >",
        "sid" : "< virtual ts3 server id, normally its 1 >"
    }
}
```

the server subcategory is for production use on your server, 
the local subcategory is for development on your local system 
 
# Run the API
start the server by running:
```
npm run dev
```
for development and
```
npm run prod
```
for running in a production environment.

If you are running this on a linux server, you can install the ts3bot.service file, then you can run this api as a service.

# PHP Compatability
Since the api only returns the query answer as plain text, we still have to parse the text. I have added an php functions file in the examples folter. 
In this file there is a function called arrayify, please don't judge me by my horrible naming of functions ... 
This arrayify function seperates all subcategorys of the returned String.
