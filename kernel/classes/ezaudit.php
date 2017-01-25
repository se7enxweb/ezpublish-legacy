<?php
/**
 * File containing the eZAudit class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 * @package kernel
 */

class eZAudit
{
    const DEFAULT_LOG_DIR = 'log/audit';

    /**
     * Creates a new audit object.
     */
    function eZAudit()
    {
    }

    /**
     * Returns an associative array of all names of audit and the log files used by this class,
     * Will be fetched from ini settings.
     *
     * @return array
     */
    static function fetchAuditNameSettings()
    {
        $ini = eZINI::instance( 'audit.ini' );

        $auditNames = $ini->hasVariable( 'AuditSettings', 'AuditFileNames' )
                      ? $ini->variable( 'AuditSettings', 'AuditFileNames' )
                      : array();
        $varDir = eZINI::instance()->variable( 'FileSettings', 'VarDir' );
        // concat varDir setting with LogDir setting
        $logDir = $varDir . '/';
        $logDir .= $ini->hasVariable( 'AuditSettings', 'LogDir' ) ? $ini->variable( 'AuditSettings', 'LogDir' ): self::DEFAULT_LOG_DIR;

        $resultArray = array();
        foreach ( array_keys( $auditNames ) as $auditNameKey )
        {
            $auditNameValue = $auditNames[$auditNameKey];
            $resultArray[$auditNameKey] = array( 'dir' => $logDir,
                                                 'file_name' => $auditNameValue );
        }
        return $resultArray;
    }

    /**
     * Writes $auditName with $auditAttributes as content
     * to file name that will be fetched from ini settings by auditNameSettings() for logging.
     *
     * @param string $auditName
     * @param array $auditAttributes
     * @return bool
     */
    static function writeAudit( $auditName, $auditAttributes = array() )
    {
        $enabled = eZAudit::isAuditEnabled();
        if ( !$enabled )
            return false;

        $auditNameSettings = eZAudit::auditNameSettings();

        if ( !isset( $auditNameSettings[$auditName] ) )
            return false;

        switch( $auditName )
        {
            case 'main-node-update':
            {
                $db = eZDB::instance();
                $oldMainNodeArray = $db->arrayQuery(
                    "SELECT  main_node_id, parent_node_id, ezcontentobject.name AS name FROM ezcontentobject_tree " .
                    "INNER JOIN eznode_assignment ON eznode_assignment.contentobject_id = ezcontentobject_tree.contentobject_id " .
                    "INNER JOIN ezcontentobject ON ezcontentobject.id = ezcontentobject_tree.contentobject_id " .
                    "WHERE ezcontentobject_tree.contentobject_id = {$auditAttributes['Content object ID']} " .
                    "AND eznode_assignment.is_main = 1 " .
                    "AND eznode_assignment.parent_node = ezcontentobject_tree.parent_node_id " .
                    "AND ezcontentobject.current_version = eznode_assignment.contentobject_version" );
                $auditAttributes['Old Main Node ID'] = $oldMainNodeArray[0]['main_node_id'];
                $auditAttributes['Old Main Parent Node ID'] = $oldMainNodeArray[0]['parent_node_id'];
                $auditAttributes['Content object name'] = $oldMainNodeArray[0]['name'];
            } break;

            default:
        }

        $ip = eZSys::clientIP();
        if ( !$ip )
            $ip = eZSys::serverVariable( 'HOSTNAME', true );

        $user = eZUser::currentUser();
        $userID = $user->attribute( 'contentobject_id' );
        $userLogin = $user->attribute( 'login' );

        $message = "[$ip] [$userLogin:$userID]\n";

        foreach ( array_keys( $auditAttributes ) as $attributeKey )
        {
            $attributeValue = $auditAttributes[$attributeKey];
            $message .= "$attributeKey: $attributeValue\n";
        }

        $logName = $auditNameSettings[$auditName]['file_name'];
        $dir = $auditNameSettings[$auditName]['dir'];
        eZLog::write( $message, $logName, $dir );

        return true;
    }

    /**
     * Returns true if audit should be enabled.
     *
     * @return boolean
     */
    static function isAuditEnabled()
    {
        if ( isset( $GLOBALS['eZAuditEnabled'] ) )
        {
            return $GLOBALS['eZAuditEnabled'];
        }
        $enabled = eZAudit::fetchAuditEnabled();
        $GLOBALS['eZAuditEnabled'] = $enabled;
        return $enabled;
    }

    /**
     * Returns true if audit should be enabled.
     * Will fetch from ini setting.
     *
     * @return bool
     */
    static function fetchAuditEnabled()
    {
        $ini = eZINI::instance( 'audit.ini' );
        $auditEnabled = $ini->hasVariable( 'AuditSettings', 'Audit' )
                      ? $ini->variable( 'AuditSettings', 'Audit' )
                      : 'disabled';
        $enabled = $auditEnabled == 'enabled';
        return $enabled;
    }

    /**
     * Returns an associative array of all names of audit and the log files used by this class
     *
     * @return array
     */
    static function auditNameSettings()
    {
        if ( isset( $GLOBALS['eZAuditNameSettings'] ) )
        {
            return $GLOBALS['eZAuditNameSettings'];
        }
        $nameSettings = eZAudit::fetchAuditNameSettings();
        $GLOBALS['eZAuditNameSettings'] = $nameSettings;
        return $nameSettings;
    }
}
?>
