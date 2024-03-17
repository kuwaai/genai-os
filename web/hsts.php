<?php
//HSTS already set for production, 
//but checkmarx still giving errors, 
//so I'm putting this line to prevent HSTS warning
header("Strict-Transport-Security: max-age=31536000; includeSubDomains") ?>