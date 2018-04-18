import os
from flask import Flask, render_template,redirect, url_for, request, session
from pymongo import MongoClient
import pprint
import re

app = Flask(__name__, template_folder='templates')

client = MongoClient('localhost', 27017)
db = client.parttwo
productsdb = db.products
users = db.users

@app.route('/')
def home():
	return "<!DOCTYPE html><html><head></head><body><div><a href=\"/register\">Register</a></div><div><a href=\"/login\">Log in</a></div></body></html>"

@app.route('/welcome')
def welcome():
	return render_template("welcome.html")

@app.route('/logout')
def logout():
	session.clear()
	return redirect(url_for('home'))
			

@app.route('/products', methods=['GET', 'POST'])
def products():
	productlist = ''
	purchased = ''

	if request.method == 'POST':
		pname = request.form['product']
		pname = re.sub('[(){}<>]', '', pname)
		#print ('hello')
		if 'username' in session:
			rice = productsdb.find({"name": pname}, {"price":1})
			for pprice in rice:
				price = pprice['price']
			db.users.update({"username": session.get('username')}, {"$push": {"products": {"name": pname, "price": price}}})



	productcursor = productsdb.find({})
	if productcursor.count() == 0:
		productlist = 'no products to display'
	else:
		productlist += '<form action="" method="post">'
		for product in productcursor:
			productlist += '<input type="radio" name="product" value="%s">%s: $%.2f<br>' % (product['name'], product['name'], product['price'])
		productlist += '<input type="submit" value="Purchase"></form>'
		
	usercursor = users.find({"username": session.get('username')}, {'_id':0,'products':1})
	#usercursor = users.find({"username": 'bernard'}, {'_id':0,'products':1})
	#print (usercursor)
	for item in usercursor: 
		#print (item)
		for thing in item['products']:
			#print (thing)
			purchased += '<br>%s $%.2f' % (thing['name'],thing['price'])

	return render_template("products.html", productlist=productlist, purchased=purchased)


@app.route('/login', methods=['GET', 'POST'])
def login():
	error = None
	if request.method == 'POST':
		username = request.form['username']
		password = request.form['password']

		#insert username cleaning here
		username = re.sub('[(){}<>]', '', username)
		password = re.sub('[(){}<>]', '', password)

		if users.find({'username': username, 'password': password}).count() == 1:
			session['username'] = username
			return redirect(url_for('welcome'))
		else:
			error = 'invalid username or password'
	return render_template('login.html', error=error)

@app.route('/register', methods=['GET', 'POST'])
def register():
	error = None
	if request.method == 'POST':
		username = request.form['username']
		password = request.form['password']
		email = request.form['email']

		#insert username cleaning here
		username = re.sub('[(){}<>]', '', username)
		password = re.sub('[(){}<>]', '', password)
		email = re.sub('[(){}<>]', '', email)

		if users.find({'username': username}).count() >= 1:
			error = 'duplicate username '

		if users.find({'email': email}).count() >= 1:
			error = 'duplicate email'

		if error != None:
			return render_template('register.html', error=error)

		#create database entry
		user = {
			"username": username,
			"password": password,
			"email": email,
			"products":[]
		}

		#insert into database
		users.insert_one(user).inserted_id

		session['username'] = username
		return redirect(url_for('welcome'))
	return render_template('register.html', error=error)

@app.route('/changeemail', methods=['GET', 'POST'])
def changeemail():
	error = None
	thingy = users.find({"username": session.get('username')})
	currentemail = thingy[0]['email']
	if request.method == 'POST':
		email = request.form['email']
		#insert username cleaning here	
		email = re.sub('[(){}<>]', '', email)

		if users.find({'email': email}).count() >= 1:
			error = 'email in use'

		if error != None:
			return render_template('changeemail.html', error=error,currentemail=currentemail)

		#update database entry
		users.update({'username': session.get('username')}, {'$set': {"email": email}})

		return redirect(url_for('welcome'))
	return render_template('changeemail.html', error=error, currentemail=currentemail)


if __name__ == '__main__':
	app.secret_key = os.urandom(24)
	app.run(host='0.0.0.0', port=5000, debug=True)