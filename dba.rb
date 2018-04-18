#!/usr/bin/ruby

require "mysql"
require "mongo"
print "Content-type: text/html\n\n"
print "<html>\n<body>\n"
print "<div style=\"font-weight: bold; \">\n"

begin
# = Mysql.new(hostname, username, password, databasename)  
db = Mysql.connect("localhost", "bernard-admin", "database", "DAAF-Database")  

results = db.query "SHOW GLOBAL STATUS LIKE 'Uptime';"


Mongo::Logger.logger.level = ::Logger::FATAL

client = Mongo::Client.new([ '127.0.0.1:27017' ])
nos = client.use('parttwo')



print "Ruby script is running!"
print "\n</div>\n"
print "MySQL:<br>"
puts "Server version:  #{db.get_server_info}<br>"
puts "Uptime:  #{(results.fetch_row[1])} seconds<br>"
print"<br>"

print '<a href="backup.py"><button type="button">Create Backup</button></a>'

print "<br>"
print "<br>"

print "MongoDB:<br>"
nos.command({"dbstats" => 1}).documents[0].each do |key, value|
	puts "#{key}: #{value}<br>"
end



nos.command({"serverStatus" => 1}).documents[0].each do |key, value|
	puts "#{key}: #{value}<br>"
end


print "</body>\n</html>"


db.close
client.close
end