#!/usr/bin/python3
# -*- coding: UTF-8 -*-# enable debugging
import cgitb
from pymongo import MongoClient
import pprint
from flask import Flask

client = MongoClient('localhost', 27017)
db = client.parttwo
products = db.products

cgitb.enable()


print("Content-Type: text/html;charset=utf-8")
print()    
print("Congratz, you finally got Python to work! :D")

for product in products.find({},{'_id':0, 'name':1}):
	pprint.pprint(product)

