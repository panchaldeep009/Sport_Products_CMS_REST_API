// The Vue build version to load with the `import` command
// (runtime-only or standalone) has been set in webpack.base.conf with an alias.
import 'vuetify/dist/vuetify.min.css';
import '@mdi/font/css/materialdesignicons.css';

import Vue from 'vue';
import Vuetify from 'vuetify';

import App from './App';

Vue.use(Vuetify, {
  iconfont: 'mdi',
});

Vue.config.productionTip = false;

/* eslint-disable no-new */
new Vue({
  el: '#app',
  components: { App },
  data: {
    admin: false,
    home:
            process.env.NODE_ENV === 'development'
              ? 'http://localhost/code/Sport_Products_CMS_REST_API/project/'
              : '',
  },
  template: '<App/>',
});
