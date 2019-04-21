// The Vue build version to load with the `import` command
// (runtime-only or standalone) has been set in webpack.base.conf with an alias.
import 'vuetify/dist/vuetify.min.css';
import '@mdi/font/css/materialdesignicons.css';
import axios from 'axios';

import Vue from 'vue';
import Vuetify from 'vuetify';

import App from './App';
import CMS from './cms';

Vue.use(Vuetify, {
  iconfont: 'mdi',
});

Vue.config.productionTip = false;

/* eslint-disable no-new */
new Vue({
  el: '#app',
  components: { App, CMS },
  data: {
    cms: false,
    user: false,
    home:
            process.env.NODE_ENV === 'development'
              ? 'http://localhost/code/Sport_Products_CMS_REST_API/project/'
              : '',
  },
  methods: {
    locationChang() {
      this.cms = window.location.hash.includes('admin');
    },
    checkLogin() {
      axios
        .get(`${this.home}admin/index.php`, {
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          withCredentials: true,
        })
        .then(({ data }) => {
          if (data.error) {
            this.error = data.error;
          } else {
            this.error = '';
            this.user = data;
          }
        });
    },
  },
  mounted() {
    this.locationChang();
    this.checkLogin();
    window.addEventListener('hashchange', () => {
      this.locationChang();
    });
  },
  template: '<CMS v-if="cms"/><App v-else/>',
});
