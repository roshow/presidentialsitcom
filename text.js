const APP_EL = document.querySelector('#app');

const API_URL = `https://cdn.contentful.com/spaces/vc1pqz55uikb/entries`;

const mdConverter = new showdown.Converter();

const headerComponent = data => {
  const path = window.location.pathname;
  return `<header>
    <div class="titleBox">
      <h3 class="title" data-type="path" data-path="/">Presidential Sitcom</h3>
    </div>
    <nav>
      <a data-type="path" data-path="/" class="${ path === '/' ? 'active' : '' }" >episodes</a>
      |
      <a data-type="path" data-path="/about" class="${ path === '/about' ? 'active' : '' }">about</a>
      |
      <a href="https://twitter.com/presidentsitcom">twitter</a>
    </nav>
  </header>`;
}

const entryComponent = (data, options={}) => {

  const { id, createdAt, contentType } = data.sys;
  const createdAtDateStr = new Date(createdAt).toLocaleDateString();
  const entryType = contentType.sys.id;

  const { title='', summary, guestAuthor, number } = data.fields;
  const summaryHtml = mdConverter.makeHtml(summary);
  const titleStr = entryType === 'episodes' ? `Episode #${ number }` :  title;


  return `<div class="entry">
    <p class="entry-title"><span class="fontBold">${ titleStr }</span</p>
    ${ summaryHtml }
    ${ guestAuthor ? `<p><span class="fontBold">by guest writer ${ guestAuthor }</span></p>` : `` }
    <p class="permalink"><a href data-type="${ entryType }" data-episode="${ number }">${ titleStr.toLowerCase() || createdAtDateStr }</a></p>
  </div>`;

}

const listComponent = (data, listType='') => `<div class="${ listType }">
  ${ data.items.reduce((htmlStr, item) => htmlStr + entryComponent(item), '') }
</div>`

const episodeListComponent = data => listComponent(data, 'episodeList');

const episodeComponent = data => `<div class="episode">
  <div data-type="episodes" class="btn btn-previous hide">« Previous</div>
  <div data-type="episodes" class="btn btn-next hide">Next »</div>
  
  <article class="clearfix">
    ${ entryComponent(data) }
  </article>
</div>`;

const detailsListComponent = data => listComponent(data, 'detailsList');

const updateEpisodeComponent = (data, el) => el.innerHTML = entryComponent(data);

const setEpisodeArrows = (selector, episode) => {
  const el = document.querySelector(selector);
  if (episode){
    el.classList.remove('hide');
    el.dataset.episode = episode.fields.number;
  }
  else {
    el.classList.add('hide');
  }
}

const defaultQuery = {
  'content_type': 'episodes',
  'limit': 99,
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

const REGEX_EPISODE_ID = /^\/episodes\/(.+?(?=\/|$))/
const REGEX_EPISODE_NUMBER = /^\/episode\/(.+?(?=\/|$))/


const router = (path, query={}) => {
  
  if (path === '/') {
    
    fetchQuery()
      .then(renderEpisodeList)
  
  }

  else if (path === '/about') {

    fetchQuery({
      content_type: 'details',
      'fields.onAboutPage': true,
      order: 'fields.aboutPagePosition'
    }).then(renderDetailsList);

  }

  else if (REGEX_EPISODE_NUMBER.test(path)) {

    fetchQuery({
      limit: 1,
      'fields.number': path.match(REGEX_EPISODE_NUMBER)[1]
    })
      .then(response => {
        renderEpisodeFromResponse(response.items.length === 0, response.items[0])
      })

  }
  
  else if (REGEX_EPISODE_ID.test(path)) {

    fetchQuery(query, path.match(REGEX_EPISODE_ID)[1])
      .then(response => {
        renderEpisodeFromResponse(response.sys.type === 'Error', response)
      })
  
  }
  
  else {

    rerouteHome()

  }

  return;
}

const rerouteHome = (query={}) => {
  history.replaceState(null, null, '/');
  router('/', query); 
}

const renderEpisodeFromResponse = (e, ...args) => {
  if (e) {
    return rerouteHome()
  }
  return renderEpisode(...args)
}

const renderFromCurrentPath = () => router(window.location.pathname)

const changePathAndRender = (path, state=null) => {
  history.pushState(state, null, path);
  return router(window.location.pathname);
}