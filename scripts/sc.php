<?php

$models = array(
  array(
    "file" => 'Comment',
    "content" => ' <?php class Comment extends Database {
    private $id;
    private $comment;
    private $fecha;
    private $hora;
    private $username;
    private $kind;
    private $id_external;
    private $response_comment; 
  }'
  ),
  array(
    "file" => 'Metadata',
    "content" => ' <?php class Metadata extends Database {
    private $id;
    private $visiteds;
    private $num_users;
  }'
  ),
  array(
    "file" => 'Collection',
    "content" => ' <?php class Collection extends Database {
    private $id;
    private $name;
    private $profile;
  }'
  ),
  array(
    "file" => 'Anime',
    "content" => ' <?php class Anime extends Database {
    private $siglas;
    private $idiomas;
    private $date_publication;
    private $date_finalization;
    private $state;
    private $kind;
    private $valorations;
    private $temporada;
  }'
  ),
  array(
    "file" => 'Media_anime',
    "content" => ' <?php class Media_anime extends Database {
  private $id;
  private $type;
  private $name;
  private $extension;
  private $anime;
}'
  ),
  array(
    "file" => 'Translation_anime',
    "content" => ' <?php class Translation_anime extends Database {
  private $id;
  private $translation;
  private $kind;
  private $lang;
  private $anime;
}'
  ),
  array(
    "file" => 'Anime_genere',
    "content" => ' <?php class Anime_genere extends Database {
  private $id;
  private $genere;
  private $anime;
}'
  ),
  array(
    "file" => 'Episode',
    "content" => ' <?php class Episode extends Database {
  private $id;
  private $anime;
  private $num;
  private $seasion;
  private $madia_type;
  private $madia_name;
  private $madia_extension;
}'
  ),
  array(
    "file" => 'Translation_episode',
    "content" => ' <?php class Translation_episode extends Database {
  private $id;
  private $translation;
  private $lang;
  private $episode;
}'
  ),
  array(
    "file" => 'Clip',
    "content" => ' <?php class Clip extends Database {
  private $id;
  private $title;
  private $episode;
  private $profile;
  private $time;
}'
  ),
  array(
    "file" => 'Episode_collection',
    "content" => ' <?php class Episode_collection extends Database {
  private $id;
  private $episode;
  private $collection;
}'
  ),
  array(
    "file" => 'Opening',
    "content" => ' <?php class Opening extends Database {
  private $id;
  private $nombre;
  private $descripcion;
  private $anime;
  private $num;
  private $seasion;
  private $madia_type;
  private $madia_name;
  private $madia_extension;
}'
  ),
  array(
    "file" => 'Ending',
    "content" => ' <?php class Ending extends Database {
  private $id;
  private $nombre;
  private $descripcion;
  private $anime;
  private $num;
  private $seasion;
  private $madia_type;
  private $madia_name;
  private $madia_extension;
}'
  ),
  array(
    "file" => 'Config_user',
    "content" => ' <?php class Config_user extends Database {
  private $id;
  private $username;
  private $limit_num_profiles;
  private $see_video_profiles_time;
}'
  ),
  array(
    "file" => 'Filter',
    "content" => ' <?php class Filter extends Database {
  private $id;
  private $code;
  private $kind;
}'
  ),
  array(
    "file" => 'Translation_filter',
    "content" => ' <?php class Translation_filter extends Database {
  private $id;
  private $translation;
  private $lang;
  private $id_external;
}'
  ),
  array(
    "file" => 'Lang',
    "content" => ' <?php class Lang extends Database {
  private $id;
  private $code;
}'
  ),
  array(
    "file" => 'Season',
    "content" => ' <?php class Season extends Database {
  private $id;
  private $title;
}'
  ),
  array(
    "file" => 'Config_profile',
    "content" => ' <?php class Config_profile extends Database {
  private $id;
  private $profile;
  private $theme;
  private $autoplay;
  private $columns;
  private $orden;
  private $lang;
  private $volume;
  private $video_velocity_default;
  private $avable_chat;
  private $default_view;
  private $avable_secret_chat;
  private $avable_history;
  private $limit_elem_collection;
  private $offline_mode;
  private $avable_response_comment;
  private $option_paginator;
  private $avable_notifications;
}'
  ),
  array(
    "file" => 'Notification',
    "content" => ' <?php class Notification extends Database {
  private $id;
  private $name;
  private $avaible;
  private $config;
}'
  ),
  array(
    "file" => 'Config_notification_profile',
    "content" => ' <?php class Config_notification_profile extends Database {
  private $id;
  private $kind;
  private $profile;
  private $sound;
}
'
  ),
  array(
    "file" => 'Anime_avaible_notifications',
    "content" => ' <?php class Anime_avaible_notifications extends Database {
  private $id;
  private $avaible;
  private $anime;
}'
  ),
  array(
    "file" => 'History',
    "content" => ' <?php class History extends Database {
  private $id;
  private $episode;
  private $profile;
  private $time;
}'
  ),
  array(
    "file" => 'User',
    "content" => ' <?php class User extends Database {
  private $username;
  private $nombre;
  private $apellidos;
  private $email;
  private $password;
  private $date_birthday;
  private $tipo;
  private $dni;
  private $acess_token;
  private $admin_token;
  private $activado;
  private $genere;
}'
  ),
  array(
    "file" => 'Group',
    "content" => ' <?php class Group extends Database {
  private $id;
  private $name;
}'
  ),
  array(
    "file" => 'UserGroup',
    "content" => ' <?php class UserGroup extends Database {
  private $id;
  private $username;
  private $group;
}'
  ),
  array(
    "file" => 'Profile',
    "content" => ' <?php class Profile extends Database {
  private $id;
  private $nombre;
  private $username;
}'
  ),

);




foreach ($models as $model) {
  $file = __DIR__ . "/../models/{$model['file']}.php";
  file_put_contents($file, $model['content']);
}
