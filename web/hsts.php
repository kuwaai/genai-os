<?php
//HSTS already set for production, 
//but checkmarx still giving errors, 
//so I'm putting this line to prevent HSTS warning
header("Strict-Transport-Security: max-age=36000; includeSubDomains"); ?>