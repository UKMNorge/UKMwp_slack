<?php

use UKMNorge\Slack\API\App;
use UKMNorge\Slack\API\Channels;
use UKMNorge\Slack\API\Users;
use UKMNorge\Slack\App\UKMApp;
use UKMNorge\Slack\Team\Teams;
use UKMNorge\Slack\User\Users as LocalUsers;
use UKMNorge\Slack\User\Write as WriteLocalUsers;
use UKMNorge\Slack\Channel\Channels as LocalChannels;
use UKMNorge\Slack\Channel\Write as WriteLocalChannels;

$teams = new Teams();

if( isset( $_GET['sync'] ) ) {
    $team = $teams->get( intval($_GET['sync'] ) );

    UKMApp::initFromBotToken( SLACK_BOT_TOKEN );
    $userdata = Users::getAll();

    foreach( $userdata->members as $memberdata ) {
        try {
            $user = LocalUsers::getBySlackId( $memberdata->team_id, $memberdata->id );
        } catch( Exception $e ) {
            $user = WriteLocalUsers::create( $memberdata->team_id, $memberdata->id, $memberdata->name );
        }
        $user->setRealName( is_null($memberdata->real_name) ? '' : $memberdata->real_name);
        $user->setAdditionalData($memberdata);
        WriteLocalUsers::save( $user );
    }

    $response_data = Channels::getAll();

    foreach( $response_data->channels as $channeldata ) {
        #echo '<pre>';var_dump($channeldata);echo '</pre>';
        try {
            $channel = LocalChannels::getBySlackId( $channeldata->id);
        } catch( Exception $e ) {
            $channel = WriteLocalChannels::create($team->getTeamId(), $channeldata->id, $channeldata->name);
        }
        $channel->setDescription( $channeldata->purpose->value);
        $channel->setAdditionalData($channeldata);

        WriteLocalChannels::save($channel);
    }
}

UKMwp_slack::addViewData('teams', $teams);