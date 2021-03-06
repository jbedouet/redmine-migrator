#!/usr/bin/env php
<?php

/**
 * Define the servers and store into the configuration.
 *
 * @param array $config
 * @return array
 */
function defineServers(array $config)
{
    if (! isset($config['source']) || ! is_array($config['source'])) {
        echo "\n\nSource Server Configuration\n===========================\n\n";
        $config['source']['URL'] = readline('Source server URL (e.g. https://some.redmine.server.com): ');
        $config['source']['key'] = readline('Source server Key (e.g. 1234567890abcdef1234567890abcdef12345678): ');
    }
    if (! isset($config['dest']) || ! is_array($config['dest'])) {
        echo "\n\nDestination Server Configuration\n================================\n\n";
        $config['dest']['URL'] = readline('Destination server URL (e.g. https://another.redmine.server.com): ');
        $config['dest']['key'] = readline('Destination server Key (e.g. 1234567890abcdef1234567890abcdef12345678): ');
    }

    return $config;
}

/**
 * Show trackers
 *
 * @param Redmine\Client $client
 */
function showTrackers($client)
{
    $trackers = $client->tracker->all();

    print "+-------+----------------------------------------------------+\n";
    printf("| %5s | %-50s |\n", 'id', 'Tracker Name');
    print "+-------+----------------------------------------------------+\n";
    foreach ($trackers['trackers'] as $tracker) {
        printf("| %5d | %-50s |\n", $tracker['id'], $tracker['name']);
    }
    print "+-------+----------------------------------------------------+\n\n";
}

/**
 * Define the tracker mapping from source trackers to destination trackers
 *
 * @param array $config
 * @param Redmine\Client $source
 * @param Redmine\Client $dest
 * @return array
 */
function defineTrackerMapping(array $config, $source, $dest)
{
    if (isset($config['tracker_map']) && is_array($config['tracker_map'])) {
        return $config;
    }

    //
    // Show Trackers
    //
    print "\nSource Issue Types\n\n";
    showTrackers($source);

    $trackers = $source->tracker->all();
    foreach ($trackers['trackers'] as $tracker) {
        print "Source Issue Type ID " . $tracker['id'] . ", name: " . $tracker['name'] . "\n";
        print "\nDestination Issue Types\n\n";
        showTrackers($dest);
        $config['tracker_map'][$tracker['id']] = readline('Enter the destination issue type ID for source issue type ' .
            $tracker['name'] . ': ');
    }

    return $config;
}

/**
 * Show statuses
 *
 * @param Redmine\Client $client
 */
function showStatuses($client)
{
    $statuses = $client->issue_status->all();

    print "+-------+----------------------------------------------------+\n";
    printf("| %5s | %-50s |\n", 'id', 'Issue Status Name');
    print "+-------+----------------------------------------------------+\n";
    foreach ($statuses['issue_statuses'] as $status) {
        printf("| %5d | %-50s |\n", $status['id'], $status['name']);
    }
    print "+-------+----------------------------------------------------+\n\n";
}

/**
 * Define the status mapping from source issue statuses to destination issue statuses
 *
 * @param array $config
 * @param Redmine\Client $source
 * @param Redmine\Client $dest
 * @return array
 */
function defineStatusMapping(array $config, $source, $dest)
{
    if (isset($config['status_map']) && is_array($config['status_map'])) {
        return $config;
    }

    //
    // Show Statuses
    //
    print "\nSource Issue Statuses\n\n";
    showStatuses($source);

    $statuses = $source->issue_status->all();
    foreach ($statuses['issue_statuses'] as $status) {
        print "Source Issue Status ID " . $status['id'] . ", name: " . $status['name'] . "\n";
        print "\nDestination Issue Statuses\n\n";
        showStatuses($dest);
        $config['status_map'][$status['id']] = readline('Enter the destination issue status ID for source issue status ' .
            $status['name'] . ': ');
    }

    return $config;
}

/**
 * Show priorities
 *
 * @param Redmine\Client $client
 */
function showPriorities($client)
{
    $priorities = $client->issue_priority->all();

    print "+-------+----------------------------------------------------+\n";
    printf("| %5s | %-50s |\n", 'id', 'Issue Priority Name');
    print "+-------+----------------------------------------------------+\n";
    foreach ($priorities['issue_priorities'] as $priority) {
        printf("| %5d | %-50s |\n", $priority['id'], $priority['name']);
    }
    print "+-------+----------------------------------------------------+\n\n";
}

/**
 * Define the priority mapping from source issue priorities to destination issue priorities
 *
 * @param array $config
 * @param Redmine\Client $source
 * @param Redmine\Client $dest
 * @return array
 */
function definePriorityMapping(array $config, $source, $dest)
{
    if (isset($config['priority_map']) && is_array($config['priority_map'])) {
        return $config;
    }

    //
    // Show Priorities
    //
    print "\nSource Issue Priorities\n\n";
    showPriorities($source);

    $priorities = $source->issue_priority->all();
    foreach ($priorities['issue_priorities'] as $priority) {
        print "Source Issue Priority ID " . $priority['id'] . ", name: " . $priority['name'] . "\n";
        print "\nDestination Issue Priorities\n\n";
        showPriorities($dest);
        $config['priority_map'][$priority['id']] = readline('Enter the destination issue priority ID for source issue priority ' .
            $priority['name'] . ': ');
    }

    return $config;
}

/**
 * Show users
 *
 * @param Redmine\Client $client
 */
function showUsers($client)
{
    $users = $client->user->all(['limit' => 100]);

    print "+-------+----------------------------------------------------+\n";
    printf("| %5s | %-50s |\n", 'id', 'User Name');
    print "+-------+----------------------------------------------------+\n";
    foreach ($users['users'] as $user) {
        printf("| %5d | %-50s |\n", $user['id'], $user['firstname'] . ' ' . $user['lastname']);
    }
    print "+-------+----------------------------------------------------+\n\n";
}

/**
 * Define the user mapping from source users to destination users
 *
 * @param array $config
 * @param Redmine\Client $source
 * @param Redmine\Client $dest
 * @return array
 */
function defineUserMapping(array $config, $source, $dest)
{

    if ( ! isset($config['user_map']) ) {
        $config['user_map'] = array();
    }
    
    $users = $source->user->all(['limit' => 100]);
    foreach ($users['users'] as $user) {
        if(isset($config['user_map'][$user['id']])) {
	    //printf("Skip user %s\n", $user['login']);
	    continue;
	}
        $config['user_map'][$user['id']] = readline('Enter the destination user ID for source user ' .
            $user['firstname'] . ' ' . $user['lastname'] . ' suggests [' . suggestUser($dest, $user) . '] : ');
	if(empty($config['user_map'][$user['id']])) {
	  $newId = createUser($dest, $user);
	  $config['user_map'][$user['id']] = $newId;
	};
    }

    return $config;
}

function suggestUser($dest, $user)
{
    $arr = $dest->user->all(['name' => $user['login']]);
    
    if( ! empty($arr['users']) ) {
      return $arr['users'][0]['id'];
    }
    
    $arr = $dest->user->all(['name' => $user['mail']]);
    if (empty($arr['users'])) {
        return '';
    }
    return $arr['users'][0]['id'];
    
}

function createUser($dest, $user)
{
    
    $create_user_attributes = [
        'login' => $user['login'],
        'firstname' => $user['firstname'],
        'lastname' => $user['lastname'],
        'mail' => $user['mail'],
        'auth_source_id' => 1, // Id of your preferred authentication mode - To Be Changed for your case
        //'mail_notification' => // Not in the API
        'must_change_passwd' => 'false',
        'generate_password' => 'false'
    ];
    
    $postResult = $dest->user->create($create_user_attributes);
    
    printf("Create user %s with ID %5d\n", (string) $postResult->login, (integer) $postResult->id);

    return (string) $postResult->id;
    
}

/**
 * Show projects
 *
 * @param Redmine\Client $client
 */
function showProjects($client)
{
    $projects = $client->project->all();

    print "+-------+----------------------------------------------------+\n";
    printf("| %5s | %-50s |\n", 'id', 'Project Name');
    print "+-------+----------------------------------------------------+\n";
    foreach ($projects['projects'] as $project) {
        printf("| %5d | %-50s |\n", $project['id'], $project['name']);
    }
    print "+-------+----------------------------------------------------+\n\n";
}

/**
 * Define the source and destination projects
 *
 * @param array $config
 * @param Redmine\Client $source
 * @param Redmine\Client $dest
 * @return array
 */
function defineProjects(array $config, $source, $dest)
{
    if (isset($config['project_map']) && is_array($config['project_map'])) {
        return $config;
    }

    print "\nSource Projects\n\n";
    showProjects($source);
    $source_project_id = readline('Select a source project ID: ');
    $config['project_map']['source_project_id'] = $source_project_id;

    print "\nDestination Projects\n\n";
    showProjects($dest);

    $config['project_map'][$source_project_id] = readline('Select a destination project ID: ');
    return $config;
}

/**
 * Show custom fields
 *
 * @param Redmine\Client $client
 */
function showCustomFields($client)
{
    $fields = $client->custom_fields->all();
    
    print "+-------+----------------------------------------------------+\n";
    printf("| %5s | %-50s |\n", 'id', 'Custom Field Name');
    print "+-------+----------------------------------------------------+\n";
    foreach ($fields['custom_fields'] as $field) {
        printf("| %5d | %-50s |\n", $field['id'], $field['name']);
    }
    print "+-------+----------------------------------------------------+\n\n";
}

/**
 * Define the custom field mapping from source fields to destination fields
 *
 * @param array $config
 * @param Redmine\Client $source
 * @param Redmine\Client $dest
 * @return array
 */
function defineCustomFieldMapping(array $config, $source, $dest)
{
    if (isset($config['custom_field_map']) && is_array($config['custom_field_map'])) {
        return $config;
    }

    print "\nSource Custom Fields\n\n";
    showCustomFields($source);

    $fields = $source->custom_fields->all();
    foreach ($fields['custom_fields'] as $field) {
        print "Source Custom Field ID " . $field['id'] . ", name: " . $field['name'] . "\n";
        print "\nDestination Custom Fields\n\n";
        showCustomFields($dest);
        $config['custom_field_map'][$field['id']] = readline('Enter the destination field ID for source field ' . $field['name'] . ': ');
    }

    return $config;
}

//
// Variables
//
$config_file = 'config.json';

// For Composer users (this file is generated by Composer)
require_once 'vendor/autoload.php';

// Grab the config so far, as an associative array
if (file_exists($config_file)) {
    $config = json_decode(file_get_contents($config_file), true);
} else {
    $config = [];
}

//
// Have the source and destination servers been defined?
//
$config = defineServers($config);
file_put_contents($config_file, json_encode($config, JSON_PRETTY_PRINT));

$source = new Redmine\Client($config['source']['URL'], $config['source']['key']);
$dest = new Redmine\Client($config['dest']['URL'], $config['dest']['key']);

//
// Have the tracker mappings been defined?
//
$config = defineTrackerMapping($config, $source, $dest);
file_put_contents($config_file, json_encode($config, JSON_PRETTY_PRINT));

//
// Have the status mappings been defined?
//
$config = defineStatusMapping($config, $source, $dest);
file_put_contents($config_file, json_encode($config, JSON_PRETTY_PRINT));

//
// Have the priority mappings been defined?
//
$config = definePriorityMapping($config, $source, $dest);
file_put_contents($config_file, json_encode($config, JSON_PRETTY_PRINT));

//
// Have the user mappings been defined?
//
$config = defineUserMapping($config, $source, $dest);
file_put_contents($config_file, json_encode($config, JSON_PRETTY_PRINT));

//
// Have the custom field mappings been defined?
//
$config = defineCustomFieldMapping($config, $source, $dest);
file_put_contents($config_file, json_encode($config, JSON_PRETTY_PRINT));

//
// Define project mapping
//
$config = defineProjects($config, $source, $dest);
file_put_contents($config_file, json_encode($config, JSON_PRETTY_PRINT));
$source_project_id = $config['project_map']['source_project_id'];
$dest_project_id = $config['project_map'][$source_project_id];

//
// Get destination server user ID to username mapping, required for setImpersonateUser()
//
$users = $dest->user->all();
$userNameMap = [];
foreach ($users['users'] as $user) {
    $userNameMap[$user['id']] = $user['login'];
}

//
// Create the project
// 
$source_project = $source->project->show($source_project_id);

$create_project_attributes = [
  'name' => $source_project['project']['name'],
  'identifier' => $source_project['project']['identifier'],
  'description' => $source_project['project']['description'],
  'homepage' => $source_project['project']['homepage'],
  'status' => $source_project['project']['status'],
  'is_public' => $source_project['project']['is_public']
];

$create_project_attributes['custom_fields'] = array();
foreach($source_project['project']['custom_fields'] as $custom_field) {
  $create_project_attributes['custom_fields'][] = [
    'id' => $config['custom_field_map'][$custom_field['id']],
    'value' => $custom_field['value']
  ];
}

$create_project_attributes['trackers'] = array();
foreach($source_project['project']['trackers'] as $tracker) {
  $create_project_attributes['tracker_ids'][] = $config['tracker_map'][$tracker['id']];
}

$create_project_attributes['enabled_module_names'] = array();
foreach($source_project['project']['enabled_modules'] as $enabled_module) {
  $create_project_attributes['enabled_module_names'][] = $enabled_module['name'];
}

if(array_key_exists('parent_id', $source_project['project'])) {
  $create_project_attributes['parent_id'] = $config['project_map'][$source_project['project']['id']];
  $create_project_attributes['inherit_members'] = $source_project['project']['inherit_members'];
}

$postResult = $dest->project->create($create_project_attributes);
$dest_project_id = $postResult->id;
echo "New project $dest_project_id created.\n";

/////////////////////////////////////////////////////////////////////////////////////
//
// Issues
//
/////////////////////////////////////////////////////////////////////////////////////

//
// Get the list of issues
// FIXME: Only does 100 issues, does not page through the entire project.
//
$issues = $source->issue->all([
    'limit'         => 1000,
    'sort'          => 'id',
    'project_id'    => $source_project_id,
]);
//print_r($issues);

foreach ($issues['issues'] as $issue) {
    
    print "Processing issue ID " . $issue['id'] . ', ' . $issue['subject'] . ', Status: ' . $issue['status']['name'] . "\n";

    // Don't process the same issue twice.
    if (! empty($config['issue_map'][$issue['id']])) {
        print "Already processed -- continuing.\n";
        continue;
    }

    // If you just wanted to try one issue you could find the issue ID and put it in here.
    // if ($issue['id'] != 174) {
    //     continue;
    // }

    // Get the issue data
    $issue_data = $source->issue->show($issue['id'], ['include' => 'attachments,journals']);
    $issue_data = $issue_data['issue'];

    // echo "\nSource Issue Data\n=================\n\n";
    // print_r($issue_data);

    // Create a new issue on the destination server
    $create_attributes = [
        'project_id'        => $dest_project_id,
        'tracker_id'        => $config['tracker_map'][$issue_data['tracker']['id']],
        'status_id'         => $config['status_map'][$issue_data['status']['id']],
        'priority_id'       => $config['priority_map'][$issue_data['priority']['id']],
        'subject'           => $issue_data['subject'],
        'description'       => $issue_data['description'],
        'assigned_to_id'    => $config['user_map'][$issue_data['assigned_to']['id']],
        'author_id'         => $config['user_map'][$issue_data['author']['id']],
    ];
    if (! empty($issue_data['estimated_hours'])) {
        $create_attributes['estimated_hours'] = $issue_data['estimated_hours'];
    }

    // echo "\nDestination Issue Data\n======================\n\n";
    // print_r($create_attributes);

    // Switch user and create the destination issue
    $dest->setImpersonateUser($userNameMap[$create_attributes['author_id']]);
    $postResult = $dest->issue->create($create_attributes);
    $dest->setImpersonateUser();

    // echo "\nPOST Results\n============\n\n";
    // print_r($postResult);
    /** @var int $new_issue_id */
    $new_issue_id = $postResult->id;
    echo "New issue $new_issue_id created.\n";

    // Add this to the issue map
    $config['issue_map'][$issue['id']] = $new_issue_id;
    file_put_contents($config_file, json_encode($config, JSON_PRETTY_PRINT));

    // Go through all of the journals and add the notes.  At this stage I'm not really interested
    // in journals that don't have notes (e.g. status changes).
    if (! empty($issue_data['journals'])) {
        foreach ($issue_data['journals'] as $journal) {
            if (empty($journal['notes'])) {
                continue;
            }
            $private_note = false;
            $dest->issue->addNoteToIssue($new_issue_id, $journal['notes'], $private_note);
            echo "Note added to issue.\n";
        }
    }

    // Go through all of the attachments and add them ot the destination server
    if (! empty($issue_data['attachments'])) {
        foreach ($issue_data['attachments'] as $attachment) {
            $file_content = $source->attachment->download($attachment['id']);
            // To upload a file + attach it to an existing issue with $issueId
            $upload = json_decode($dest->attachment->upload($file_content));
            $dest->issue->attach($new_issue_id, [
                'token'         => $upload->upload->token,
                'filename'      => $attachment['filename'],
                'description'   => $attachment['description'],
            ]);
            echo "Attachment added to issue.\n";
        }
    }
}

/////////////////////////////////////////////////////////////////////////////////////
//
// Wikis
//
/////////////////////////////////////////////////////////////////////////////////////

$wikis = $source->wiki->all($source_project_id);
$wikis = $wikis['wiki_pages'];
// print_r($wikis);

foreach ($wikis as $wiki) {

    print 'Processing wiki page ' . $wiki['title'] . "\n";

    // Don't process the same page twice.
    if (! empty($config['wiki_page_map'][$wiki['title']])) {
        print "Already processed -- continuing.\n";
        continue;
    }
    
    // To limit migration to one page only, uncomment this.
    // if ($wiki['title'] != 'Wiki') {
    //     continue;
    // }

    // Get the page contents
    $page = $source->wiki->show($source_project_id, $wiki['title']);
    $page = $page['wiki_page'];

    // Create the new page on the destination server
    $dest->wiki->create($dest_project_id, $wiki['title'], [
        'text' => $page['text'],
    ]);
    echo "New page title " . $wiki['title'] . " created.\n";

    // Save this to the wiki page map
    $config['wiki_page_map'][$wiki['title']] = $wiki['title'];
    file_put_contents($config_file, json_encode($config, JSON_PRETTY_PRINT));

    // Go through all of the attachments and add them ot the destination server
    if (! empty($page['attachments'])) {
        foreach ($page['attachments'] as $attachment) {
            $file_content = $source->attachment->download($attachment['id']);

            // Currently the php-redmine-api doesn't support attaching files to a wiki page
            // so we just store the file locally and ask the user to attach.
            file_put_contents($attachment['filename'], $file_content);
            echo "Please upload file " . $attachment['filename'] . " to page " . $wiki['title'] . "\n";

            /*
            // To upload a file + attach it to an existing issue with $issueId
            $upload = json_decode($dest->attachment->upload($file_content));
            $result = $dest->wiki->update($dest_project_id, $wiki['title'], [
                'text'      => $page['text'],
                'uploads'   => [0 => [
                    'token'         => $upload->upload->token,
                    'filename'      => $attachment['filename'],
                ]]
            ]);
            print "=====\n";
            print_r($result);
            print "=====\n";
            echo "Attachment " . $attachment['filename'] . " added to wiki page.\n";
            */
        }
    }
}

$dest->project->remove($dest_project_id);
