<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="x-ua-compatible" content="ie=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <?php include "metatags.php" ?>
        
        <link rel="shortcut icon" href="/favicon.ico">

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/4.2.0/normalize.min.css">
        <link href="https://fonts.googleapis.com/css?family=PT+Mono|Source+Code+Pro|Ubuntu+Mono" rel="stylesheet">

        <style type="text/css">        

          html {
            box-sizing: border-box;
          }
          *, *:before, *:after {
            box-sizing: inherit;
          }

          body {
            background: #D3D3D3;
            font-family: 'PT Mono', monospace;
          }

          h1, h2, h3, h4, h5 {
            margin: 0;
          }

          header {
            text-align: center;
            margin: 40px 0 0;
          }

          .titleBox .title {
            cursor: pointer;
            margin-bottom: 8px;
          }

          header .credits, header a {
            color: gray;
          }

          nav {
            font-size: 0.83em;
            color: gray;
          }

          nav .active {
            color: #000000;
          }

          .titleBox {
            display: inline-block;
            text-align: left;
          }

          #app {
            width: 100%;
            max-width: 600px;
            margin: auto;
          }

          .clearfix { clear:both; }

          .displayNone { display: none; }

          .entry {
            background: #ffffff;
            padding: 25px 40px;
            margin: 40px 0;
            font-size: 14px;
          }

          p, .entry-title {
            margin: 14px 0;
          }
          
          p, ul li {
            line-height: 1.5em;
          }

          a {
            text-decoration: none;
            cursor: pointer;
            color: #696969;
          }

          a:hover { color: #000000; }


          .btn {
            font-size: 14px;            
            display: inline-block;
            cursor: pointer;
            font-weight: 900;
            width: 130px;
            padding: 10px;
            color: #696969;
          }

          .btn:hover { color: #000000; }

          .btn.hide{ display: none }

          .btn.btn-previous { float: left; }

          .btn.btn-next {
            float: right;
            text-align: right;
          }

          .permalink {
            text-align: right;
            margin: 0;
            font-size: 11px;
            text-decoration: underline;
          }

          .episode .permalink,
          .detailsList .permalink,
          .episode .entry-title,
          .episodeList .entry-title { display: none; }

          .episode .entry { margin-bottom: 10px; }

          .guest-writer {
            font-weight: bold;
          }

          .detailsList a { text-decoration: underline; }


          

        </style>
    </head>
    <body>

          

        <div id="app"></div>

        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/fetch/2.0.1/fetch.js"></script>
        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/showdown/1.5.4/showdown.min.js"></script>
        <script type="text/javascript">

          const APP_EL = document.querySelector('#app');

          const API_URL = `https://cdn.contentful.com/spaces/vc1pqz55uikb/entries`;
          
          const mdConverter = new showdown.Converter();

          const headerComponent = data => {
            const path = window.location.pathname;
            return `<header>
              <div class="titleBox">
                <h3 class="title" data-type="path" data-path="/">A Presidential Sitcom</h3>
              </div>
              <nav>
                <a data-type="path" data-path="/" class="${ path === '/' ? 'active' : '' }" >episodes</a>
                |
                <a data-type="path" data-path="/about" class="${ path === '/about' ? 'active' : '' }">about</a>
              </nav>
            </header>`;
          }

          const entryComponent = (data, options={}) => {

            const { title='', summary, guestAuthor, number } = data.fields;
            const summaryHtml = mdConverter.makeHtml(summary);

            const { id, createdAt } = data.sys;
            const createdAtDateStr = new Date(createdAt).toLocaleDateString();

            return `<div class="entry">
              <h4 class="entry-title">${ title }</h4>
              ${ guestAuthor ? `<p><span class="guest-writer">Guest Writer: ${ guestAuthor }</span></p>` : `` }
              ${ summaryHtml }
              <p class="permalink"><a href data-type="episode" data-episode="${ id }">
                ${ number >= 0 ? `episode #${ number}` : createdAtDateStr }
              </a></p>
            </div>`;

          }

          const listComponent = (data, listType='') => `<div class="${ listType }">
            ${ data.items.reduce((htmlStr, item) => htmlStr + entryComponent(item), '') }
          </div>`

          const episodeListComponent = data => listComponent(data, 'episodeList');

          const episodeComponent = data => `<div class="episode">
            <article>
              ${ entryComponent(data) }
            </article>
            <div data-type="episode" class="btn btn-previous hide">« Previous</div>
            <div data-type="episode" class="btn btn-next hide">Next »</div>
            <div class="clearfix"></div>
          </div>`;

          const detailsListComponent = data => listComponent(data, 'detailsList');

          const updateEpisodeComponent = (data, el) => el.innerHTML = entryComponent(data);

          const setEpisodeArrows = (selector, episode) => {
            const el = document.querySelector(selector);
            if (episode){
              el.classList.remove('hide');
              el.dataset.episode = episode.sys.id;
            }
            else {
              el.classList.add('hide');
            }
          }

          const defaultQuery = {
            'content_type': 'episodes',
            'limit': 100,
            'order': '-fields.number',
            'access_token': '1676b21629539cf0be8b7d7df2a3cb0fd9343767ffd512ea74065aaca9755bc7'
          }
          
          const queryParams = (params={}) => {
            const allParams = Object.assign({}, defaultQuery, params);
            return Object.keys(allParams)
                .map(k => encodeURIComponent(k) + '=' + encodeURIComponent(allParams[k]))
                .join('&');
          }

          const fetchQuery = (params, id='') => fetch(`${ API_URL }/${ id }?${ queryParams(params) }`)
            .then(response => response.json());

          const renderEpisodeList = data => APP_EL.innerHTML = headerComponent() + episodeListComponent(data);

          const renderDetailsList = data => {
            APP_EL.innerHTML = headerComponent() + detailsListComponent(data);
            APP_EL.querySelectorAll('a[href]').forEach(el => el.target = '_blank');
          }

          const renderEpisode = data => {
            const el = document.querySelector('.episode article');
            if (el) {
              el.innerHTML = updateEpisodeComponent(data, el);
            }
            else {
              APP_EL.innerHTML = headerComponent() + episodeComponent(data);  
            }
            
            /* FETCH PREVIOUS EPISODE */
            fetchQuery({
              limit: 1,
              'fields.number': data.fields.number - 1
            }).then(response => setEpisodeArrows('.btn-previous', response.items[0]));
            
            /* FETCH NEW EPISODE */
            fetchQuery({
              limit: 1,
              'fields.number': data.fields.number + 1
            }).then(response => setEpisodeArrows('.btn-next', response.items[0]));
          }
          

          const fetchAndRender = (query, path) => fetchQuery(query, path).then(render); 

          const REGEX_EPISODE = /^\/episodes\/(.+?(?=\/|$))/

          const router = (path, query={}) => {
            
            if (path === '/') {
              
              fetchQuery().then(renderEpisodeList);
            
            }

            else if (path === '/about') {

              fetchQuery({
                content_type: 'details',
                'fields.onAboutPage': true,
                order: 'fields.aboutPagePosition'
              }).then(renderDetailsList);

            }
            
            else if (REGEX_EPISODE.test(path)) {
              
              fetchQuery(query, path.match(REGEX_EPISODE)[1]).then(renderEpisode);
            
            }
            
            else {

              history.replaceState(null, null, '/');
              fetchAndRender(); 

            }

            return;
          }


          const changePathAndRender = (path, state=null) => {
            history.pushState(state, null, path);
            return router(window.location.pathname);
          }

          APP_EL.addEventListener('click', e => {
            
            const dataset = e.target.dataset;
            if (dataset.type) {
              e.preventDefault();
              e.stopPropagation();
              switch (dataset.type) {
                case 'path':
                  return changePathAndRender(dataset.path);
                case 'episode':
                  return changePathAndRender(`/episodes/${ dataset.episode }`);
                default:
                  return;
              }
            }
          });
          window.addEventListener('popstate', e => renderFromCurrentPath()); 

          router(window.location.pathname);

        </script>
    </body>
</html>