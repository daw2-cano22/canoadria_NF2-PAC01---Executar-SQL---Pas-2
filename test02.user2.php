<?php

        require("class.user2.php");
        require("class.pdofactory.php");

        print "Running...<br />";

        $strDSN = "pgsql:host=localhost;port=5432;dbname=chapterseven";
        $objPDO = PDOFactory::GetPDO($strDSN, "postgres", "root",array());
        $objPDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $objUser = new User($objPDO);
        $objUser->setFirstName("Steve");
        $objUser->setLastName("Nowicki");
        $objUser->setDateAccountCreated(date("Y-m-d"));
        print "First name is " . $objUser->getFirstName() . "<br />";
        print "Last name is " . $objUser->getLastName() . "<br />";
        print "Saving...<br />";
        
        $objUserB = new User($objPDO);
        $objUserB->setFirstName("Jose");
        $objUserB->setLastName("Fernandez");
        $objUserB->setDateAccountCreated(date("Y-m-d"));

        $objUserC = new User($objPDO);
        $objUserC->setFirstName("Pablo");
        $objUserC->setLastName("Segura");
        $objUserC->setDateAccountCreated(date("Y-m-d"));
        print "First name is " . $objUserB->getFirstName() . "<br />";
        print "Last name is " . $objUserB->getLastName() . "<br />";
        print "Saving...<br />";
        print "First name is " . $objUserC->getFirstName() . "<br />";
        print "Last name is " . $objUserC->getLastName() . "<br />";
        print "Saving...<br />";

        $objUser->Save();
        $objUserB->Save();
        $objUserC->Save();
   
        print "ID in database is " . $objUser->getID() . "<br />";
        print "ID in database is " . $objUserB->getID() . "<br />";
        print "ID in database is " . $objUserC->getID() . "<br />";
?>
