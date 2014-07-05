<?php

/**
 * Description of tuplitApiSetup
 * This class is used to setup the tuplit api
 *
 * @author 
 */
use RedBean_Facade as R;
use Enumerations\AccountType as AccountType;
use Helpers\PasswordHelper as PasswordHelper;

class tuplitApiSetup {

    /**
     *
     * We aim to create data structures here and insert base data into them
     */
    public static function init(){

        /**
         * Nuke the database
         */
        R::nuke();

        /**
         * Run the sql scripts for oauth2 library
         */
        $sql_file = '../vendor/league/oauth2-server/sql/mysql.sql';
        $contents = file_get_contents($sql_file);
        R::exec($contents);

        /**
         * Setup database
         */
        $sql_file = '../sql/db.sql';
        $contents = file_get_contents($sql_file);
        R::exec($contents);

        /**
         * Create the admin account
         * @var User $accountAdmin
         */
        $accountAdmin = R::dispense('user');
        $accountAdmin->Email = '';
        $accountAdmin->Name = '';
        $accountAdmin->UserName = 'v';
        $accountAdmin->password = PasswordHelper::encrypt('password');
        $accountAdmin->createdDate = time();
        $accountAdmin->modifiedDate = $accountAdmin->dateCreated;
        R::store($accountAdmin);

        
    }
}