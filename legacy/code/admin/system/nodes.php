<?php
  // +-------------------------------------------------------------------+
  // | Appleseed Web Community Management Software                       |
  // | http://appleseed.sourceforge.net                                  |
  // +-------------------------------------------------------------------+
  // | FILE: nodes.php                               CREATED: 06-14-2007 + 
  // | LOCATION: /code/admin/system/                MODIFIED: 06-14-2007 +
  // +-------------------------------------------------------------------+
  // | Copyright (c) 2004-2008 Appleseed Project                         |
  // +-------------------------------------------------------------------+
  // | This program is free software; you can redistribute it and/or     |
  // | modify it under the terms of the GNU General Public License       |
  // | as published by the Free Software Foundation; either version 2    |
  // | of the License, or (at your option) any later version.            |
  // |                                                                   |
  // | This program is distributed in the hope that it will be useful,   |
  // | but WITHOUT ANY WARRANTY; without even the implied warranty of    |
  // | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the     |
  // | GNU General Public License for more details.                      |	
  // |                                                                   |
  // | You should have received a copy of the GNU General Public License |
  // | along with this program; if not, write to:                        |
  // |                                                                   |
  // |   The Free Software Foundation, Inc.                              |
  // |   59 Temple Place - Suite 330,                                    | 
  // |   Boston, MA  02111-1307, USA.                                    |
  // |                                                                   |
  // |   http://www.gnu.org/copyleft/gpl.html                            |
  // +-------------------------------------------------------------------+
  // | AUTHORS: Michael Chisari <michael.chisari@gmail.com>              |
  // +-------------------------------------------------------------------+
  // | VERSION:     0.7.3                                                |
  // | DESCRIPTION: Node administration page.                            |
  // +-------------------------------------------------------------------+

  eval(_G); // Import all global variables  
  
  // Change to document root directory
  chdir ($_SERVER['DOCUMENT_ROOT']);

  // Include BASE API classes.
  require_once ('legacy/code/include/classes/BASE/application.php'); 
  require_once ('legacy/code/include/classes/BASE/debug.php'); 
  require_once ('legacy/code/include/classes/base.php'); 
  require_once ('legacy/code/include/classes/system.php'); 
  require_once ('legacy/code/include/classes/BASE/remote.php'); 
  require_once ('legacy/code/include/classes/BASE/tags.php'); 
  require_once ('legacy/code/include/classes/BASE/xml.php'); 

  // Include Appleseed classes.
  require_once ('legacy/code/include/classes/appleseed.php'); 
  require_once ('legacy/code/include/classes/privacy.php'); 
  require_once ('legacy/code/include/classes/friends.php'); 
  require_once ('legacy/code/include/classes/messages.php'); 
  require_once ('legacy/code/include/classes/users.php'); 
  require_once ('legacy/code/include/classes/auth.php'); 
  require_once ('legacy/code/include/classes/search.php'); 

  // Create the Application class.
  $zOLDAPPLE = new cAPPLESEED ();
  
  // Set Global Variables (Put this at the top of wrapper scripts)
  $zOLDAPPLE->SetGlobals ();

  // Initialize Appleseed.
  $zOLDAPPLE->Initialize("admin.system.nodes", TRUE);

  // Create local classes.
  $ADMINDATA = new cSYSTEMNODES ($zOLDAPPLE->Context);

  // Load security settings for the current page.
  $zLOCALUSER->Access (FALSE, FALSE, FALSE);

  // Load admin strings into cache.
  cLanguage::Load ('en-US', 'system.admin.lang');

  // Check to see if user has read access for this area.
  if ($zLOCALUSER->userAccess->r == FALSE) {

    $zOLDAPPLE->IncludeFile ('legacy/code/site/error/403.php', INCLUDE_SECURITY_NONE);
    $zOLDAPPLE->End();

  } // if

  // Create a warning message if user has no write access.
  if ($zLOCALUSER->userAccess->w == FALSE) {
    $ADMINDATA->Message = __("Write Access Denied");
    $ADMINDATA->Error = 0;
  } // if

  // Set the page title.
  $gPAGESUBTITLE = ' - Admin';

  // Set how much to step when scrolling.
  $gSCROLLSTEP[$zOLDAPPLE->Context] = 10;

  // Set the post data to move back and forth.
  $gPOSTDATA = Array ("CRITERIA"        => $gCRITERIA,
                      "SCROLLSTART"     => $gSCROLLSTART,
                      "SORT"            => $gSORT);

  // Set which switch to highlight.
  global $gSelectedSwitch;
  $gSelectedSwitch['admin_system'] = 'selected';

  // Set which tab to highlight.
  global $gSelectedTab;
  $gSelectedTab['admin_system_nodes'] = 'selected';

  // Display the select all button by default.
  $gSELECTBUTTON = 'Select All';

  // Change the select button if anything is eelected.
  if ($zOLDAPPLE->ArrayIsSet ($gMASSLIST) ) $gSELECTBUTTON = 'Select None';

  // PART I: Determine appropriate action.
  switch ($gACTION) {
    case 'SELECT_ALL':
      $gSELECTBUTTON = 'Select None';
    break;

    case 'SELECT_NONE':
      $gMASSLIST = array ();
      $gSELECTBUTTON = 'Select All';
    break;

    case 'MOVE_UP':
      // Check if any items were selected.
      if (!$gMASSLIST) {
        $ADMINDATA->Message = __("None Selected");
        $ADMINDATA->Error = -1;
        break;
      } // if

      // Loop through the list
      foreach ($gMASSLIST as $number => $tidvalue) {
        $ADMINDATA->tID = $tidvalue;
        $ADMINDATA->Move(UP, $tidvalue);
        // Move in the selected list.
        if (!$ADMINDATA->Error) $gMASSLIST[$number] = $tidvalue - 1;
      } // if

    break;

    case 'MOVE_DOWN':
      // Check if any items were selected.
      if (!$gMASSLIST) {
        $ADMINDATA->Message = __("None Selected");
        $ADMINDATA->Error = -1;
        break;
      } // if

      // Loop through the list
      foreach ($gMASSLIST as $number => $tidvalue) {
        $ADMINDATA->tID = $tidvalue;
        $ADMINDATA->Move(DOWN, $tidvalue);
        // Move in the selected list.
        if (!$ADMINDATA->Error) $gMASSLIST[$number] = $tidvalue + 1;
      } // if

    break;

    case 'DELETE_ALL':
      // Check if any items were selected.
      if (!$gMASSLIST) {
        $ADMINDATA->Message = __("None Selected");
        $ADMINDATA->Error = -1;
        break;
      } // if

      // Loop through the list
      foreach ($gMASSLIST as $number => $tidvalue) {
        $ADMINDATA->tID = $tidvalue;
        $ADMINDATA->Delete();
        if (!$ADMINDATA->Error) $datalist[$number] = $tidvalue;
      } // if

      // If no errors, output a successful message.
      if (!$ADMINDATA->Error) {
        global $gDATALIST; $gDATALIST = implode(", ", $datalist);
        // Use proper grammer depending on how many records chosen.
        if (count ($gMASSLIST) == 1) {
          $ADMINDATA->Message = __("Record Deleted", array ('id' => $datalist[0]));
        } else {
          $ADMINDATA->Message = __("Records Deleted");
        } // if
        unset ($gDATALIST);
        unset ($gMASSLIST);
      } // if
    break;

    case 'SAVE':
      // Check if user has write access;
      if ($zLOCALUSER->userAccess->w == FALSE) {
        $ADMINDATA->Message = __("Write Access Denied");
        $ADMINDATA->Error = -1;
        break;        
      } // if

      // Synchronize Data
      $ADMINDATA->Synchronize();
      $ADMINDATA->EndStamp = $zHTML->JoinDate ('ENDSTAMP');
      $ADMINDATA->Stamp = SQL_SKIP;
      if ($gNEVER[0] == TRUE) $ADMINDATA->EndStamp = STAMP_NEVER;
      if (!$ADMINDATA->tID) $ADMINDATA->Stamp = SQL_NOW;
      if (!$ADMINDATA->Source) $ADMINDATA->Source = $gSITEDOMAIN;
      if ($ADMINDATA->tID == "") {
        $ADMINDATA->tID = 1;
        $ADMINDATA->Sanity();
        if (!$ADMINDATA->Error) {
          $ADMINDATA->Add();
          $ADMINDATA->Message = __("Record Added", array ('id' => $ADMINDATA->LastIncrement));
        } // if
      } else {
        $ADMINDATA->Sanity();
        if (!$ADMINDATA->Error) {
          $ADMINDATA->Message = __("Record Updated", array ('id' => $ADMINDATA->tID));
          $ADMINDATA->Update();
        } // if
      } // if
    break;

    case 'NEW':
    break;

    case 'EDIT':
    
    break;

    case 'DELETE':
      // Check if user has write access;
      if ($zLOCALUSER->userAccess->w == FALSE) {
        $ADMINDATA->Message = __("Write Access Denied");
        $ADMINDATA->Error = -1;
        break;        
      } // if

      // Synchronize Data
      $ADMINDATA->Synchronize();
      $ADMINDATA->Delete();
      if (!$ADMINDATA->Error) {
        $ADMINDATA->Message = __("Record Deleted", array ('id' => $ADMINDATA->tID));
        $ADMINDATA->Update();
      } // if
      $ADMINDATA->Select("", "", $gSORT);
    break;

    default:
    break;
  } // switch

  // PART II: Load the necessary data from the database.
  switch ($gACTION) {

    case 'EDIT':
      $ADMINDATA->Synchronize();
      $ADMINDATA->Select ("tID", $ADMINDATA->tID);
      $ADMINDATA->FetchArray();
      
      global $gSTAMPLIST;
      $gSTAMPLIST = $zHTML->SplitDate ($ADMINDATA->Stamp); 
      
      global $gNEVERCHECKED;
      if ($ADMINDATA->EndStamp == STAMP_NEVER) $gNEVERCHECKED = TRUE;
    break;

    case 'SELECT_ALL':
    case 'DELETE_ALL':
    case 'MOVE_UP':
    case 'MOVE_DOWN':
    case 'SAVE':
    case 'DELETE':
    default:
      if ($gCRITERIA) {
        $ADMINDATA->SelectByAll($gCRITERIA, $gSORT, 1);

        // If only one result, jump right to edit form.
        if ( ($ADMINDATA->CountResult() == 1) AND ($gACTION == 'SEARCH') ) {
          // Fetch the data.
          $ADMINDATA->FetchArray();
          $ADMINDATA->Select ("tID", $ADMINDATA->tID);
          $gACTION = 'EDIT';
          $gCRITERIA = ''; $gPOSTDATA['CRITERIA'] = '';
        } // if
      } else {
        $ADMINDATA->Select("", "", $gSORT);
      } // if
    break;
    
  } // switch

  // PART III: Pre-parse the html for the main window. 
  
  // Buffer the main listing.
  ob_start ();  

  if ($zLOCALUSER->userAccess->a == TRUE) {
    // Choose an action
    switch ($gACTION) {
      case 'EDIT':
        $zOLDAPPLE->IncludeFile ("$gFRAMELOCATION/objects/admin/system/nodes/edit.aobj", INCLUDE_SECURITY_NONE);
      break;
      case 'NEW':
        $zOLDAPPLE->IncludeFile ("$gFRAMELOCATION/objects/admin/system/nodes/new.aobj", INCLUDE_SECURITY_NONE);
      break;
      case 'SELECT_ALL':
      case 'DELETE_ALL':
      case 'MOVE_UP':
      case 'MOVE_DOWN':
      case 'SAVE':
        // Skip to the default.
      default:
        if ( ($ADMINDATA->Error == 0) OR 
             ($gACTION == 'DELETE_ALL') OR 
             ($gACTION == 'MOVE_UP') OR
             ($gACTION == 'MOVE_DOWN') ) {

          $zOLDAPPLE->IncludeFile ("$gFRAMELOCATION/objects/admin/system/nodes/list.top.aobj", INCLUDE_SECURITY_NONE);

          // Calculate scroll values.
          $gSCROLLMAX[$zOLDAPPLE->Context] = $ADMINDATA->CountResult();

          // Adjust for a recently deleted entry.
          $zOLDAPPLE->AdjustScroll ('admin.system.nodes', $ADMINDATA);

          // Check if any results were found.
          if ($gSCROLLMAX[$zOLDAPPLE->Context] == 0) {
            $ADMINDATA->Message = __("No Results Found");
            $ADMINDATA->Broadcast();
          } // if

          // Loop through the list.
          $target = "_admin/system/nodes/";
          for ($listcount = 0; $listcount < $gSCROLLSTEP[$zOLDAPPLE->Context]; $listcount++) {
           if ($ADMINDATA->FetchArray()) {
            $output = $zOLDAPPLE->Format ($ADMINDATA->Output, FORMAT_VIEW);
            if ($gACTION == 'SELECT_ALL') $checked = TRUE;

            $gEXTRAPOSTDATA['ACTION'] = "EDIT"; 
            $gEXTRAPOSTDATA['tID']    = $ADMINDATA->tID;
            $ADMINDATA->FormatVerboseDate ('EndStamp');
            global $gTRUSTED;
            switch ($ADMINDATA->Trust) {
              case NODE_TRUSTED:
                $ADMINDATA->Message = __("Node Trusted");
                $gTRUSTED = __("Node Trusted");
              break;
              case NODE_BLOCKED:
                $ADMINDATA->Message = __("Node Blocked");
                $gTRUSTED = __("Node Blocked");
              break;
            } // switch
      
            if ($ADMINDATA->EndStamp == STAMP_NEVER) {
              $ADMINDATA->fEndStamp = __("Node Never Expires");
            } // if
            
            $zOLDAPPLE->IncludeFile ("$gFRAMELOCATION/objects/admin/system/nodes/list.middle.aobj", INCLUDE_SECURITY_NONE);
            unset ($gEXTRAPOSTDATA);

           } else {
            break;
           } // if
          } // for

          $zOLDAPPLE->IncludeFile ("$gFRAMELOCATION/objects/admin/system/nodes/list.bottom.aobj", INCLUDE_SECURITY_NONE);

        } elseif ( ($gACTION == 'SAVE') or ($gACTION == 'DELETE') ) {
          if ($gtID) {
            $zOLDAPPLE->IncludeFile ("$gFRAMELOCATION/objects/admin/system/nodes/edit.aobj", INCLUDE_SECURITY_NONE);
          } else {
            $zOLDAPPLE->IncludeFile ("$gFRAMELOCATION/objects/admin/system/nodes/new.aobj", INCLUDE_SECURITY_NONE);
          } // if
        } // if
      break;
    } // switch
  } else {
    // Access Denied
    $zOLDAPPLE->IncludeFile ("$gFRAMELOCATION/objects/admin/common/denied.aobj", INCLUDE_SECURITY_NONE);
  } // if

  // Retrieve output buffer.
  $bMAINSECTION = ob_get_clean (); 
  
  // End buffering.
  ob_end_clean (); 

  // Include the outline frame.
  $zOLDAPPLE->IncludeFile ("$gFRAMELOCATION/frames/admin/system/nodes.afrw", INCLUDE_SECURITY_NONE);

  // End the application.
  $zOLDAPPLE->End ();

?>
