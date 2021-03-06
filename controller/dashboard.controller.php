<?php

use UKMNorge\Slack\API\Channels;
use UKMNorge\Slack\API\Users;
use UKMNorge\Slack\App\UKMApp;
use UKMNorge\Slack\Cache\Team\Teams;
use UKMNorge\Slack\Cache\User\Users as LocalUsers;
use UKMNorge\Slack\Cache\User\Write as WriteLocalUsers;
use UKMNorge\Slack\Cache\Channel\Channels as LocalChannels;
use UKMNorge\Slack\Cache\Channel\Write as WriteLocalChannels;

$teams = new Teams();

if( isset( $_GET['sync'] ) ) {
    $team = $teams->get( intval($_GET['sync'] ) );

    UKMApp::initFromBotToken( $team->getBot()->getAccessToken() );
    $userdata = Users::getAll();

    foreach( $userdata->members as $memberdata ) {
        try {
            $user = LocalUsers::getBySlackId( $memberdata->team_id, $memberdata->id );
        } catch( Exception $e ) {
            $user = WriteLocalUsers::create( $memberdata->team_id, $memberdata->id, $memberdata->name );
        }
        $user->setRealName( is_null($memberdata->real_name) ? '' : $memberdata->real_name);
        $user->setAdditionalData($memberdata);
        if( $memberdata->deleted ) {
            $user->deactivate();
        } else {
            $user->activate();
        }
        WriteLocalUsers::save( $user );
    }

    $response_data = Channels::getAll();

    foreach( $response_data->channels as $channeldata ) {
        #echo '<pre>';var_dump($channeldata);echo '</pre>';
        try {
            $channel = LocalChannels::getBySlackId( $team->getTeamId(), $channeldata->id);
        } catch( Exception $e ) {
            $channel = WriteLocalChannels::create($team->getTeamId(), $channeldata->id, $channeldata->name);
        }
        $channel->setDescription( $channeldata->purpose->value);
        $channel->setAdditionalData($channeldata);

        WriteLocalChannels::save($channel);
    }
}

UKMwp_slack::addViewData('teams', $teams);