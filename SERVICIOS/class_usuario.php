<?php
	class Usuario{
		//Declaracion de los atributos
		var $userID;
		var $nombre;
		var $apellido;
		var $sexo;
		var $fechaNacimiento;
		var $rango;
		var $email;
		var $password;
		var $userHistory;
		//Declaracion del constructor
		function __construct( $nombre, $apellido, $sexo, $fechaNacimiento, $rango, $email, $password ) {
  			$this->nombre=$nombre;
  			$this->apellido=$apellido;
  			$this->sexo=$sexo;
  			$this->fechaNacimiento=$fechaNacimiento;
  			$this->rango=$rango;
  			$this->email=$email;
			$this->password=$password;
			$this->userHistory= array();
		}
		//Declaracion de los metodos
		//Metodo que regresa el nombre del usuario
		function getUserName(){
			return $this->nombre." ".$this->apellido;
		}
		//Metodo para llenar informacion del usuario con query a la base de datos
		function populateDataFromDatabase($userID){
			//Declaracion de variables
			global $conexionMySQL;
			//preparar query
			$query='SELECT * FROM usuario WHERE userID="'.$userID.'"';
			//ejecutar query
			$result=$conexionMySQL->query($query);
			//ver si tenemos resultados
			if(!$result){
				return false;
				free($result);
			}
			//ver si tenemos un elemento
			if($result->num_rows==1){
				//Conseguir los datos del usuario y llenar la informacion del objeto
				$row=$result->fetch_assoc();
				$this->nombre=$row["nombre"];
				$this->apellido=$row["apellido"];
				$this->sexo=$row["sexo"];
				$this->fechaNacimiento=$row["fechaNacimiento"];
				$this->rango=$row["rango"];
				$this->email=$row["email"];
				$this->password=$row["password"];
				//acutalizar el ID del usuario
				$this->userID=$userID;
				return true;
			}else{
				return false;
				free($result);
			}
		}
		//Metodo que inserta el usuario en la base de datos
		function sendUserToDatabase(){
			//Declaracion de variables
			global $conexionMySQL;
			//prepara query
			$query='INSERT INTO `usuario`(`userID`, `nombre`, `apellido`, `sexo`, `fechaNacimiento`, `rango`, `email`, `password`) 
					VALUES (UUID(),"'.$this->nombre.'","'.$this->apellido.'","'.$this->sexo.'","'.$this->fechaNacimiento.'","'.$this->rango.'","'.$this->email.'","'.$this->password.'")';
			//hacer query a la base de datos
			$result=$conexionMySQL->query($query);
			//verificar
			if($result){
				return true;
				free($result);
			}else{
				free($result);
				return false;
			}
		}
		//metodo para llenar el historial del usuario
		function populateUserHistory(){
			//Declaracion de variables
			global $conexionMySQL;
			//Preparamos query
			$query="SELECT * FROM presion WHERE pacienteID='".$this->userID."'";
			//hacer query
			$result=$conexionMySQL->query($query);
			//ver si tenemos resultados
			if($result){
				//ciclo for para iterar por los valores
				while($row=$result->fetch_assoc()){
					//Creamos una nueva presion
					$newPresion= new Presion($row["presionID"],$row["presionSist"],$row["presionDist"],$row["presionSistManual"],$row["presionDistManual"]);
					array_push($this->userHistory,$newPresion);
				}
				return true;
			}
			else{
				free($result);
				return false;
			}
		}
		//metodo para regresar el historial de las presiones en formato JSON
		function getUserHistory(){
			//Declaracion de variables
			$returnJson="[";
			//Ciclo for para iterar por el historial
			for($i=0; $i<sizeof($this->userHistory); $i++){
				$returnJson=$returnJson.$this->userHistory[$i]->getJsonString();
				//si no estamos al final entonces meter la coma
				if($i!=(sizeof($this->userHistory)-1)){$returnJson=$returnJson.",";}
			}
			$returnJson=$returnJson."]";
			return $returnJson;
		}
	}
?>