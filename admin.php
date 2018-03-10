<?php

session_start();
if(!isset($_SESSION['username']) || empty($_SESSION['username'])){
  header("location: login.php");
  exit;
}
if($_SESSION['admin'] == 0) header("location: index.html");

$servername = "localhost";
$username = "bernard-admin";
$password = "database";
$dbname = "DAAF-Database";
$showcustomers = "";
$sql = "";
$error = FALSE;

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 


//this is the show customers 'fuction'
if(isset($_POST['showcustomers'])) {

    $sql = "SELECT * FROM Customers";
	$result = $conn->query($sql);

	if ($result->num_rows > 0) {
	    // output data of each row
	    while($row = $result->fetch_assoc()) {
	        $showcustomers .= "id: " . $row["customerid"]. " - Name: " . $row["givenname"]. " " . $row["lastname"]. " - Email: ". $row["email"]. "<br>";
	    }
	} else {
	    $showcustomers = "0 customers in table";
	}

}

//this is the show products 'fuction'
if(isset($_POST['showproducts'])) {

    $sql = "SELECT * FROM Products";
	$result = $conn->query($sql);

	if ($result->num_rows > 0) {
	    // output data of each row
	    while($row = $result->fetch_assoc()) {
	        $showproducts .= "id: " . $row["productid"]. " - Name: " . $row["name"]. " - Price: $". $row["price"]. " - Description: ". $row["description"]. "<br>";
	    }
	} else {
	    $showproducts = "0 products in table";
	}

}

//this is the change product price 'fuction'
if(isset($_POST['changeprice'])) {

	if(!isset($_POST['price']) || !isset($_POST['productid'])) $error = TRUE;

		$taglessprice = filter_var($_POST['price'], FILTER_SANITIZE_STRING);
		$taglessproductid = filter_var($_POST['productid'], FILTER_SANITIZE_STRING);

		$cleanprice = htmlentities($taglessprice);
		$cleanprouductid = htmlentities($taglessproductid);

		if (strlen($cleanprice) > 512 || strlen($cleanprouductid) > 512) $error = TRUE;

		$price = $cleanprice;
		$productid = $cleanprouductid;

		$stmt = $conn->prepare("UPDATE Products SET price = ? WHERE productid = ?");
		$stmt->bind_param("ss", $price, $productid);

		if (!$error){
			if ($stmt->execute()){
				$changeprice = "added sucessfully";
			} else{
				$changeprice = "connection to add failed";
		}
		} else {
			$changeprice = "error in input";
		}
		
		
}

//this is the remove product 'fuction'
if(isset($_POST['removeproduct'])) {

	if(!isset($_POST['productid'])) $error = TRUE;

		$taglessproductid = filter_var($_POST['productid'], FILTER_SANITIZE_STRING);

		$cleanprouductid = htmlentities($taglessproductid);

		if (strlen($cleanprouductid) > 512) $error = TRUE;

		$productid = $cleanprouductid;

		$stmt = $conn->prepare("DELETE FROM Products WHERE productid = ?");
		$stmt->bind_param("s", $productid);

		if (!$error){
			if ($stmt->execute()){
				$removeproduct = "removed sucessfully";
			} else{
				$removeproduct = "connection to remove failed";
		}
		} else {
			$removeproduct = "error in input";
		}
		
		
}

//this is the add customer 'function'
if(isset($_POST['addcustomer'])) {

	$stmt = $conn->prepare("INSERT INTO Customers (givenname, lastname, email) VALUES (?,?,?)");
	$stmt->bind_param("sss", $givenname, $lastname, $email);

	//checks for blank inputs
	if(!isset($_POST['givenname']) ||
	 !isset($_POST['lastname']) ||
	 !isset($_POST['email'])) {
	 	$error = TRUE;
	}

	//gets rid of tags
	$taglessgivenname = filter_var($_POST['givenname'], FILTER_SANITIZE_STRING);
	$taglesslastname = filter_var($_POST['lastname'], FILTER_SANITIZE_STRING);
	$taglessemail = filter_var($_POST['email'], FILTER_SANITIZE_STRING);

	//makes it html compliant
	$cleangivenname = htmlentities($taglessgivenname);
	$cleanlastname = htmlentities($taglesslastname);
	$cleanemail = htmlentities($taglessemail);

	//checks string length
	if (strlen($cleangivenname) > 512 ||
		strlen($cleanlastname) > 512 ||
		strlen($cleanemail) > 512){
		$error = TRUE;
	}

	//sanitizes email
	$sanitizedemail = filter_var($cleanemail, FILTER_SANITIZE_EMAIL);
	$validemail = filter_var($sanitizedemail, FILTER_VALIDATE_EMAIL);

	if (!$error){

		//set all variables
		$givenname = $cleangivenname;
		$lastname = $cleanlastname;
		$email = $validemail;

		if ($stmt->execute()){
		//$sql = "INSERT INTO Customers (givenname, lastname, email) VALUES ('".$givenname."','".$lastname."','".$email."')";
		//if ($conn->query($sql)){
			$addcustomer = "added sucessfully";
		} else{
			$addcustomer = "connection to add failed";
			//$addcustomer = $stmt;
		}
	} else {
		$addcustomer = "error in input";
	}

}

//this is the add product 'function'
if(isset($_POST['addproduct'])) {

	$stmt = $conn->prepare("INSERT INTO Products (name, price, description) VALUES (?,?,?)");
	$stmt->bind_param("sss", $name, $price, $description);

	//checks for blank inputs
	if(!isset($_POST['name']) ||
	 !isset($_POST['price']) ||
	 !isset($_POST['description'])) {
	 	$error = TRUE;
	}

	//gets rid of tags
	$taglessname = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
	$taglessprice = filter_var($_POST['price'], FILTER_SANITIZE_STRING);
	$taglessdescription = filter_var($_POST['description'], FILTER_SANITIZE_STRING);

	//makes it html compliant
	$cleanname = htmlentities($taglessname);
	$cleanprice = htmlentities($taglessprice);
	$cleandescription = htmlentities($taglessdescription);

	//checks string length
	if (strlen($cleanname) > 512 ||
		//$cleanprice < 0 ||
		strlen($cleandescription) > 512){
		$error = TRUE;
	}

	if (!$error){

		//set all variables
		$name = $cleanname;
		$price = $cleanprice;
		$description = $cleandescription;

		if ($stmt->execute()){
		//$sql = "INSERT INTO Customers (givenname, lastname, email) VALUES ('".$givenname."','".$lastname."','".$email."')";
		//if ($conn->query($sql)){
			$addproduct = "added sucessfully";
		} else{
			$addproduct = "connection to add failed";
			//$addresult = $stmt;
		}
	} else {
		$addproduct = "error in input";
	}

}

$conn->close();

?>

<html>
<head>
</head>
<body>


<div><p><a href="index.html">Home</a>|<a href="admin.php">Admin Site (PHP)</a>|<a href="login.php">Log In</a>|<a href="logout.php">Log Out</a></p></div>



<form action="" method="POST">
	<input type="submit" value="Show Customers" name="showcustomers">
</form>
<div><?php echo $showcustomers; ?></div>

<p>-----------------------------------------------------------</p>

<form action="" method="POST">
	<div>Given Name: <input type="text" name="givenname">  </div><br />
	<div>Last Name: <input type="text" name="lastname">  </div><br />
	<div>Email: <input type="text" name="email">  </div>
	<input type="submit" value="Add New Customer" name="addcustomer">
</form>
<div><?php echo $addcustomer; ?></div>

<p>-----------------------------------------------------------</p>

<form action="" method="POST">
	<input type="submit" value="Show Products" name="showproducts">
</form>
<div><?php echo $showproducts; ?></div>

<p>-----------------------------------------------------------</p>

<form action="" method="POST">
	<div>Name: <input type="text" name="name">  </div><br />
	<div>Price ($): <input type="text" name="price">  </div><br />
	<div>Description: <input type="text" name="description">  </div>
	<input type="submit" value="Add New Product" name="addproduct">
</form>
<div><?php echo $addproduct; ?></div>

<p>-----------------------------------------------------------</p>

<form action="" method="POST">
	<div>Product ID: <input type="text" name="productid">  </div><br />
	<div>New Price ($): <input type="text" name="price">  </div><br />
	<input type="submit" value="Change Product Price" name="changeprice">
</form>
<div><?php echo $changeprice; ?></div>

<p>-----------------------------------------------------------</p>

<form action="" method="POST">
	<div>Product ID: <input type="text" name="productid">  </div>
	<input type="submit" value="Remove Product" name="removeproduct">
</form>
<div><?php echo $removeproduct; ?></div>

</body>
</html>
