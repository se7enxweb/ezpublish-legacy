<?php
//
// Created on: <21-Nov-2003 11:37:53 amos>
//
// Copyright (C) 1999-2003 eZ systems as. All rights reserved.
//
// This source file is part of the eZ publish (tm) Open Source Content
// Management System.
//
// This file may be distributed and/or modified under the terms of the
// "GNU General Public License" version 2 as published by the Free
// Software Foundation and appearing in the file LICENSE.GPL included in
// the packaging of this file.
//
// Licencees holding valid "eZ publish professional licences" may use this
// file in accordance with the "eZ publish professional licence" Agreement
// provided with the Software.
//
// This file is provided AS IS with NO WARRANTY OF ANY KIND, INCLUDING
// THE WARRANTY OF DESIGN, MERCHANTABILITY AND FITNESS FOR A PARTICULAR
// PURPOSE.
//
// The "eZ publish professional licence" is available at
// http://ez.no/products/licences/professional/. For pricing of this licence
// please contact us via e-mail to licence@ez.no. Further contact
// information is available at http://ez.no/home/contact/.
//
// The "GNU General Public License" (GPL) is available at
// http://www.gnu.org/copyleft/gpl.html.
//
// Contact licence@ez.no if any conditions of this licencing isn't clear to
// you.
//

include_once( "kernel/common/template.php" );
include_once( "kernel/classes/ezpackage.php" );
include_once( "kernel/classes/ezpackagecreationhandler.php" );
include_once( "lib/ezutils/classes/ezhttptool.php" );

$module =& $Params['Module'];

$http =& eZHTTPTool::instance();

$creator = false;
$runStep = false;
if ( $module->isCurrentAction( 'CreatePackage' ) )
{
    $creatorID = $module->actionParameter( 'CreatorItemID' );
    if ( $creatorID )
    {
        $creator =& eZPackageCreationHandler::instance( $creatorID );
        $persistentData = array();
        $http->setSessionVariable( 'eZPackageCreatorData' . $creatorID, $persistentData );
        $runStep = true;
    }
}
else if ( $module->isCurrentAction( 'PackageStep' ) )
{
    if ( $module->hasActionParameter( 'CreatorItemID' ) )
    {
        $creatorID = $module->actionParameter( 'CreatorItemID' );
        $creator =& eZPackageCreationHandler::instance( $creatorID );
        if ( $http->hasSessionVariable( 'eZPackageCreatorData' . $creatorID ) )
            $persistentData = $http->sessionVariable( 'eZPackageCreatorData' . $creatorID );
        else
            $persistentData = array();
    }
}

$tpl =& templateInit();

$templateName = "design:package/create.tpl";
if ( $creator )
{
    $currentStepID = false;
    if ( $module->hasActionParameter( 'CreatorStepID' ) )
        $currentStepID = $module->actionParameter( 'CreatorStepID' );
    $steps =& $creator->stepMap();
    if ( !isset( $steps['map'][$currentStepID] ) )
        $currentStepID = $steps['first']['id'];
    $errorList = array();
    $hasAdvanced = false;

    $package = false;
    if ( isset( $persistentData['package_name'] ) )
        $package =& eZPackage::fetch( $persistentData['package_name'] );

    $lastStepID = $currentStepID;
    if ( $module->hasActionParameter( 'NextStep' ) )
    {
        $hasAdvanced = true;
        print( "Advance<br/>" );
        $currentStepID = $creator->advanceStep( $package, $http, $currentStepID, $steps, $persistentData, $errorList );
        if ( $currentStepID != $lastStepID )
            $runStep = true;
    }

    if ( $currentStepID )
    {
        $currentStep =& $steps['map'][$currentStepID];

        $stepTemplate = $creator->stepTemplate( $currentStep );
        $stepTemplateName = $stepTemplate['name'];
        $stepTemplateDir = $stepTemplate['dir'];

        if ( $runStep )
            $creator->runStep( $package, $http, $currentStep, $persistentData, $tpl );

        if ( $package )
            $persistentData['package_name'] = $package->attribute( 'name' );

        $http->setSessionVariable( 'eZPackageCreatorData' . $creatorID, $persistentData );

        $tpl->setVariable( 'creator', $creator );
        $tpl->setVariable( 'current_step', $currentStep );
        $tpl->setVariable( 'persistent_data', $persistentData );
        $tpl->setVariable( 'error_list', $errorList );
        $tpl->setVariable( 'package', $package );

        $templateName = "design:package/$stepTemplateDir/$stepTemplateName";
    }
    else
    {
        $creator->finalize( $package, $http, $persistentData );
        $package->setAttribute( 'is_active', true );
        $http->removeSessionVariable( 'eZPackageCreatorData' . $creatorID );
        if ( $package )
            return $module->redirectToView( 'view', array( 'full', $package->attribute( 'name' ) ) );
        else
            return $module->redirectToView( 'list' );
    }
}
else
{
    $creators =& eZPackageCreationHandler::creatorList();

    $tpl->setVariable( 'creator_list', $creators );
}

$Result = array();
$Result['content'] =& $tpl->fetch( $templateName );
$Result['path'] = array( array( 'url' => false,
                                'text' => ezi18n( 'kernel/package', 'Create package' ) ) );

?>
