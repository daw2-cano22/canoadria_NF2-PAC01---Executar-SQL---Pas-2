<?php

class User {

        private $ID;
        private $objPDO;
        private $strTableName;
        private $arRelationMap;
        private $blForDeletion;

        private $FirstName;
        private $LastName;
        private $Username;
        private $Password;
        private $EmailAddress;

        private $DateLastLogin;
        private $TimeLastLogin;
        private $DateAccountCreated;
        private $TimeAccountCreated;
/*CREEM UNA FUNCIO que genera un array amb totes les columnes*/
        public function __construct(PDO $objPDO, $id = NULL) {
                $this->strTableName = "system_user";
                $this->arRelationMap = array(
                        "id" => "ID",
                        "first_name" => "FirstName",
                        "last_name" => "LastName",
                        "username" => "Username",
                        "md5_pw" => "Password",
                        "email_address" => "EmailAddress",
                        "date_last_login" => "DateLastLogin",
                        "time_last_login" => "TimeLastLogin",
                        "date_account_created" => "DateAccountCreated",
                        "time_account_created" => "TimeAccountCreated");
                $this->objPDO = $objPDO;
                /*Emagatzema les estrictures*/
                if (isset($id)) {
                        $this->ID = $id;
                        $strQuery = "SELECT ";
                        foreach ($this->arRelationMap as $key => $value) {
                                $strQuery .= "\"" . $key . "\",";
                        }
                        /*Afegim a la query per cada element*/
                        $strQuery = substr($strQuery, 0, strlen($strQuery)-1);
                        /*Per ada taula segon la id*/
                        $strQuery .= " FROM " . $this->strTableName . " WHERE \"id\" = :eid";
                        /*Preparem la query per posterior executar*/
                        $objStatement = $this->objPDO->prepare($strQuery);
                        /*Assignem els parametres*/
                        $objStatement->bindParam(':eid', $this->ID, PDO::PARAM_INT);
                        /*Excutem la query final*/
                        $objStatement->execute();
                        /*obtenim el strmember i el valor per a cada element*/
                        $arRow = $objStatement->fetch(PDO::FETCH_ASSOC);
                        foreach($arRow as $key => $value) {
                                $strMember = $this->arRelationMap[$key];
                                if (property_exists($this, $strMember)) {
                                        if (is_numeric($value)) {
                                                eval('$this->' . $strMember . ' = ' . $value . ';');
                                        } else {
                                                eval('$this->' . $strMember . ' = "' . $value . '";');
                                        };
                                };
                        };
                };
                /*definim la query en cas de que no existeixi*/                
                $definition = '"id" serial primary key, "first_name" varchar(120), "last_name" varchar(120), "username" varchar(120), "md5_pw" varchar(120), "email_address" varchar(120), "date_last_login" date, "time_last_login" date, "date_account_created" date, "time_account_created" date';                
                /*generem la creacio de la taula*/
                $strQuery = 'CREATE TABLE IF NOT EXISTS "system_user" (' . $definition . ');';
                
                unset($objStatement);
                /*Preparem la query emagatzemada i despres la executem */
                $objStatement = $objPDO->prepare($strQuery);
                $objStatement->execute();
        }
        
        public function Save() {
                /*Realitzem una actualitzacio de la taula si existeix o afegin si no esta*/
                if (isset($this->ID)) {
                        $strQuery = 'UPDATE "' . $this->strTableName . '" SET ';
                        foreach ($this->arRelationMap as $key => $value) {
                                eval('$actualVal = &$this->' . $value . ';');
                                if (isset($actualVal)) {
                                        $strQuery .= '"' . $key . "\" = :$value, ";
                                };
                        }
                        /*Obte el caracter començamt per 0 a la posicio del strquery menys 2*/
                        $strQuery = substr($strQuery, 0, strlen($strQuery)-2);
                        /*Concatena el where id = parametre a la strquery aterior*/
                        $strQuery .= ' WHERE "id" = :eid';
                        unset($objStatement);
                        /*Treuem la statement i preparem la quey.Despres assignem els parametres :eid i evaluem el valor parametritzat*/
                        $objStatement = $this->objPDO->prepare($strQuery);
                        $objStatement->bindValue(':eid', $this->ID, PDO::PARAM_INT);
                        foreach ($this->arRelationMap as $key => $value) {
                                        eval('$actualVal = &$this->' . $value . ';');
                                        if (isset($actualVal)) {
                                                if ((is_int($actualVal)) ||
                                                   ($actualVal == NULL)) {
                                                        $objStatement->bindValue(':' . $value, $actualVal, PDO::PARAM_INT);
                                                } else {
                                                        $objStatement->bindValue(':' . $value, $actualVal, PDO::PARAM_STR);
                                                };
                                        };
                        };
                        $objStatement->execute();
                } else {
                        /*la funcio execute realitza inserts amb parametres*/
                        $strValueList = "";
                        $strQuery = 'INSERT INTO "' . $this->strTableName . '"(';
                        foreach ($this->arRelationMap as $key => $value) {
                                eval('$actualVal = &$this->' . $value . ';');
                                if (isset($actualVal)) {
                                         $strQuery .= '"' . $key . '", ';
                                         $strValueList .= ":$value, ";
                                };
                        }
                        $strQuery = substr($strQuery, 0, strlen($strQuery) - 2);
                        $strValueList = substr($strValueList, 0, strlen($strValueList) - 2);
                        $strQuery .= ") VALUES (";
                        $strQuery .= $strValueList;
                        $strQuery .= ")";
                        unset($objStatement);
                        $objStatement = $this->objPDO->prepare($strQuery);
                        foreach ($this->arRelationMap as $key => $value) {
                                eval('$actualVal = &$this->' . $value . ';');
                                if (isset($actualVal)) {
                                        if ((is_int($actualVal)) || 
                                            ($actualVal == NULL)) {
                                                $objStatement->bindValue(':' . $value, $actualVal, PDO::PARAM_INT);
                                        } else {
                                                $objStatement->bindValue(':' . $value, $actualVal, PDO::PARAM_STR);
                                        };
                                };
                        }
                        /*Executa la query*/
                        $objStatement->execute();
                        /*Afegeix una id*/
                        $this->ID = $this->objPDO->lastInsertId($this->strTableName . "_id_seq");
                }
        }

        public function MarkForDeletion() {
                $this->blForDeletion = true;
        }

        public function __destruct() {
                /*Aquesta funcio es el decontructor. Inicia quan la funcio es destrueix. Executa un delete a la taula segons una ID*/
                if (isset($this->ID)) {
                        if ($this->blForDeletion == true) {
                                $strQuery = 'DELETE FROM "' . $this->strTableName . '" WHERE "id" = :eid';
                                $objStatement = $this->objPDO->prepare($strQuery);
                                $objStatement->bindValue(':eid', $this->ID, PDO::PARAM_INT);
                                $objStatement->execute();
                        };
                }
        }

        public function __call($strFunction, $arArguments) {

                $strMethodType = substr($strFunction, 0, 3);
                $strMethodMember = substr($strFunction, 3);
                switch ($strMethodType) {
                        case "set":
                                return($this->SetAccessor($strMethodMember, $arArguments[0]));
                                break;
                        case "get":
                                return($this->GetAccessor($strMethodMember));
                };
                return(false);
        }

        private function SetAccessor($strMember, $strNewValue) {
                if (property_exists($this, $strMember)) {
                        if (is_numeric($strNewValue)) {
                                eval('$this->' . $strMember . ' = ' . $strNewValue . ';');
                        } else {
                                eval('$this->' . $strMember . ' = "' . $strNewValue . '";');
                        };
                } else {
                        return(false);
                };
        }

        private function GetAccessor($strMember) {
                if (property_exists($this, $strMember)) {
                        eval('$strRetVal = $this->' . $strMember . ';');
                        return($strRetVal);
                } else {
                        return(false);
                };
        }
}
?>