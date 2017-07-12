<?php
  
  $title = 'Presidential Sitcom';
  $site_url = 'http://presidentialsit.com';
  $site_description = "A rich old white guy says so much crazy stuff that he's elected President. Now he, the First Family, and the new White House staff must figure out how to run an entire country!";
  $uri = $_SERVER["REQUEST_URI"];
  $is_episode = preg_match_all("/^\/episode\/([a-zA-Z0-9]+)/", $uri, $episode_matches_out);
  $is_about = preg_match_all("/^\/about$/", $uri);

  if ($is_episode) {

    $id = $episode_matches_out[1][0];

    $url = 'https://cdn.contentful.com/spaces/vc1pqz55uikb/entries/?content_type=episodes&limit=1&order=-fields.number&access_token=1676b21629539cf0be8b7d7df2a3cb0fd9343767ffd512ea74065aaca9755bc7&fields.number=' . $id

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true ); 
    $response = curl_exec($ch);
    curl_close($ch);

    $response = json_decode($response);
    $json = $response[0]
    $description = $json->fields->summary;
    $url = $site_url . '/episodes/' . $id;
    $title .= ' | Episode #' . $json->fields->number;

  }

  else if ($is_about) {
    $title .= ' | About';
    $description = 'All the dirty details about Presidential Sitcom';
    $url = $site_url . '/about';
  }

  else {

    $description = $site_description;
    $url = $site_url;
  }

?>

<meta data-uri="<?= $uri ?>">
<title><?= $title ?></title>
<meta name="description" content="<?= $description ?>">

<meta name="twitter:card" value="summary">

<meta property="og:title" content="<?= $title ?>" />
<meta property="og:type" content="article" />
<meta property="og:description" content="<?= $description ?>" />
<meta property="og:url" content="<?= $url ?>" />
<meta property="og:image" content="//roshow.net/images/presidentialsitcom_block.jpg" />

