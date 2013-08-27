<?php
/**
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @copyright Copyright Anton Terekhov, NetMonsters LLC, 2011-2013
 * @license https://github.com/AntonTerekhov/OrientDB-PHP/blob/master/LICENSE
 * @link https://github.com/AntonTerekhov/OrientDB-PHP
 * @package OrientDB-PHP
 */

/**
 * Main class in OrientDB tests
 *
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @package OrientDB-PHP
 * @subpackage Tests
 */
abstract class OrientDB_TestCase extends PHPUnit_Framework_TestCase
{

    /**
     * Correct password for root can be found at
     * config/orientdb-server-config.xml in your OrientDB installation
     * @var string
     */
    protected $root_password = '60F3D52B4374C22B19F2EA5AD2812A45FB1C34985C2532D60E267AADB9E3E130';

    /**
     * Instance of OrientDB-PHP
     * @var OrientDB
     */
    protected $db;

    protected function getClusterIdByClusterName($info, $cluster_name)
    {
        foreach ($info['clusters'] as $cluster_info) {
            if ($cluster_info->name === $cluster_name) {
                return $cluster_info->id;
            }
        }
        return false;
    }

    protected function getClusterNameByClusterId($info, $cluster_id)
    {
        foreach ($info['clusters'] as $cluster_info) {
            if ($cluster_info->id === $cluster_id) {
                return $cluster_info->name;
            }
        }
        return false;
    }
}