<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="x-ua-compatible" content="ie=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <?php include metatags.php ?>
        
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
          }
          
          p {
            font-size: 14px;
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

          .episode .permalink { display: none; }

          .episode .entry { margin-bottom: 10px; }

        </style>
    </head>
    <body>

          

        <div id="app"></div>

        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/fetch/2.0.1/fetch.js"></script>
        <script type="text/javascript">

          const apiurl = `https://cdn.contentful.com/spaces/vc1pqz55uikb/entries`;
          
          const defaultQuery = {
            'content_type': 'episodes',
            'limit': 100,
            'order': '-sys.createdAt',
            'access_token': '1676b21629539cf0be8b7d7df2a3cb0fd9343767ffd512ea74065aaca9755bc7'
          }
          
          const queryParams = (params={}) => {
            const allParams = Object.assign({}, defaultQuery, params);
            return Object.keys(allParams)
                .map(k => encodeURIComponent(k) + '=' + encodeURIComponent(allParams[k]))
                .join('&');
          }

          const fetchQuery = (params, id='') => fetch(`${ apiurl }/${ id }?${ queryParams(params) }`)
            .then(response => response.json());

          const headerComponent = data => `<header>
            <div class="titleBox">
              <h3 class="title" data-type="path" data-path="/">A Presidential Sitcom</h3>
              <h5 class="credits">by <a href="https://twitter.com/roshow" target="_blank">roshow</a></h5>
            </div>
          </header>`;

          const entryComponent = data => `<div class="entry">
            <p>${ data.fields.summary }</p>
            <p class="permalink"><a href data-type="episode" data-episode="${ data.sys.id }">${ new Date(data.sys.createdAt).toLocaleString() }</a></p>
          </div>`;

          const episodeListComponent = data => `<div class="episodeList">
            ${ data.items.reduce((htmlStr, item) => htmlStr + entryComponent(item), '') }
          </div>`;

          const episodeComponent = data => `<div class="episode">
            <article>
              ${ entryComponent(data) }
            </article>
            <div data-type="episode" class="btn btn-previous hide">« Previous</div>
            <div data-type="episode" class="btn btn-next hide">Next »</div>
            <div class="clearfix"></div>
          </div>`;

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


          const appEl = document.querySelector('#app');
          
          const render = data => {

            switch (data.sys.type) {
              
              case 'Entry':
                
                const el = document.querySelector('.episode article');
                if (el) {
                  el.innerHTML = updateEpisodeComponent(data, el);
                }
                else {
                  appEl.innerHTML = headerComponent() + episodeComponent(data);  
                }
                
                /* FETCH PREVIOUS EPISODE */
                fetchQuery({ limit: 1,  [`sys.createdAt[lt]`]: data.sys.createdAt}).then(response => setEpisodeArrows('.btn-previous', response.items[0]));
                
                /* FETCH NEW EPISODE */
                fetchQuery({ limit: 1, [`sys.createdAt[gt]`]: data.sys.createdAt, order: 'sys.createdAt' }).then(response => setEpisodeArrows('.btn-next', response.items[0]));

                return;

              case 'Array':
                
                return appEl.innerHTML = headerComponent() + episodeListComponent(data);

              case 'Error':
              default:
                
                console.warn('response error', data);
                return appEl.innerHTML = headerComponent() + `<h2>500 Error</h2>`;
            }
          };

          const pathRegex = /^\/episodes\/(.+?(?=\/|$))/

          const renderFromPath = (path, query) => fetchQuery(query, ( path.match(pathRegex) || [] )[1] ).then( data => {
            return render(data);
          });

          const renderFromCurrentPath = query => renderFromPath(window.location.pathname);

          const changePath = (path, renderMethod, state=null) => {
            history.pushState(state, null, path);
            return renderMethod(path);
          }

          const changePathAndRender = (path, state) => changePath(path, renderFromPath, state);

          appEl.addEventListener('click', e => {
            
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

          renderFromCurrentPath();

        </script>
    </body>
</html>