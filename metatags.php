<?php

  $siteurl = 'http://presidentialsit.com';
  $uri = $_SERVER["REQUEST_URI"];
  $is_match = preg_match_all("/^\/episodes\/([a-zA-Z0-9]+)/", $uri, $matches_out);

  if ($is_match) {

    $id = $matches_out[1][0];

    $url =  'https://cdn.contentful.com/spaces/vc1pqz55uikb/entries/' . $id . '?content_type=episodes&limit=100&order=-sys.createdAt&access_token=1676b21629539cf0be8b7d7df2a3cb0fd9343767ffd512ea74065aaca9755bc7';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true ); 
    $json = curl_exec($ch);
    curl_close($ch);

    $json = json_decode($json);
    $description = $json->fields->summary;
    $url = $siteurl . '/episodes/' . $id;
  }

  else {

    $description = "A rich old white guy says so much crazy and angry stuff that he becomes President. But now he's got to figure out how to run a whole country!";
    $url = $siteurl;
  }

?>


<title>A Presidential Sitcom</title>
<meta name="description" content="<?= $description ?>">

<meta name="twitter:card" value="summary">

<meta property="og:title" content="A Presidential Sitcom" />
<meta property="og:type" content="article" />
<meta property="og:description" content="<?= $description ?>" />
<meta property="og:url" content="<?= $url ?>" />
<!-- <meta property="og:image" content="http://example.com/image.jpg" /> -->

