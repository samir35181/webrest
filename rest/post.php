<?php
include "config.php";
include "utils.php";


    
  // Allow from any origin
  if (isset($_SERVER['HTTP_ORIGIN'])) {
      // Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one
      // you want to allow, and if so:
      header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
      header('Access-Control-Allow-Credentials: true');
      header('Access-Control-Max-Age: 86400');    // cache for 1 day
  }
  
  // Access-Control headers are received during OPTIONS requests
  if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
      
      if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
          // may also be using PUT, PATCH, HEAD etc
          header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");         
      
      if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
          header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
  
      exit(0);
  }
  
  
  


$dbConn =  connect($db);


/*
  listar todos los posts o solo uno
 */
if ($_SERVER['REQUEST_METHOD'] == 'GET')
{
    if (isset($_GET['id']))
    {
      //Mostrar un post
      $sql = $dbConn->prepare("SELECT * FROM pacientes where id=:id");
      $sql->bindValue(':id', $_GET['id']);
      $sql->execute();
      header("HTTP/1.1 200 OK");
      echo json_encode(  $sql->fetch(PDO::FETCH_ASSOC)  );
      exit();
	  }
    else {
      //Mostrar lista de post
      $sql = $dbConn->prepare("SELECT * FROM pacientes");
      $sql->execute();
      $sql->setFetchMode(PDO::FETCH_ASSOC);
      header("HTTP/1.1 200 OK");
      echo json_encode( $sql->fetchAll()  );
      exit();
	}
}

// Crear un nuevo post
if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
    $input = $_POST;
    $sql = "INSERT INTO pacientes
          (nombre, apellidos , ciudad, antecedentes, alergias, medicamentos)
          VALUES
          (:nombre, :apellidos, :ciudad, :antecedentes, :alergias, :medicamentos)";
    $statement = $dbConn->prepare($sql);
    bindAllValues($statement, $input);
    $statement->execute();
    $postId = $dbConn->lastInsertId();
    if($postId)
    {
      $input['id'] = $postId;
      header("HTTP/1.1 200 OK");
      echo json_encode($input);
      exit();
	 }
}

//Borrar
if ($_SERVER['REQUEST_METHOD'] == 'DELETE')
{
	$id = $_GET['id'];
  $statement = $dbConn->prepare("DELETE FROM pacientes where id=:id");
  $statement->bindValue(':id', $id);
  $statement->execute();
	header("HTTP/1.1 200 OK");
	exit();
}

//Actualizar
if ($_SERVER['REQUEST_METHOD'] === 'PUT')
{
  $put=file_get_contents("php://input");
  $puts= explode("&",$put);
  $put_vars= json_decode($put);
  //var_dump($put_vars);
  //echo $put_vars->{'id'};
    $input = $put_vars;
    $postId = $input->{'id'};
    $fields = getParams($input);
    //echo $input['id'];
    $sql = "
          UPDATE pacientes
          SET $fields
          WHERE id='$postId'
           ";

    $statement = $dbConn->prepare($sql);
    bindAllValues($statement, $input);

    $statement->execute();
    header("HTTP/1.1 200 OK");
    exit();
}


//En caso de que ninguna de las opciones anteriores se haya ejecutado
//if ($_SERVER['REQUEST_METHOD'] === 'PUT')
//{
 // parse_str(file_get_contents("php://input"),$put_vars);
//  echo $put_vars['id'];
//  var_dump($put_vars);
//  $sql= "UPDATE posts SET pelicula=:pelicula, Genero=:Genero, observaciones=:observaciones, correo=:correo, ciudad=:ciudad WHERE id=:id";
//  $statement = $dbConn->prepare($sql);
//  $statement->bindValue(':pelicula',$put_vars['pelicula']);
//  $statement->bindValue(':Genero',$put_vars['Genero']);
//  $statement->bindValue(':observaciones',$put_vars['observaciones']);
//  $statement->bindValue(':correo',$put_vars['correo']);
//  $statement->bindValue(':ciudad',$put_vars['ciudad']);
//  $statement->bindValue(':id',$put_vars['id']);
//  $statement->execute();
//  header("HTTP/1.1 200 OK");
//    exit();

  
   
//}

header("HTTP/1.1 400 Bad Request");
