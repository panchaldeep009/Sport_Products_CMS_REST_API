<template>
  <div>
    <v-layout row justify-space-between>
      <div>
        <v-btn color="blue" v-if="cmsRoute != 'home'" dark @click="cmsRoute = 'home'">WEB VIEW</v-btn>
        <v-btn
          color="yellow"
          @click="cmsRoute = 'create_user'"
          v-if="$root.user && $root.user.user_name == 'Admin' && cmsRoute != 'create_user'"
        >Create User</v-btn>
        <v-btn
          color="green"
          v-if="$root.user && !thisProduct && cmsRoute != 'create_product'"
          @click="cmsRoute = 'create_product'"
          dark
        >Create Product</v-btn>
        <v-btn
          color="green"
          v-if="$root.user && thisProduct && cmsRoute != 'edit_product'"
          @click="cmsRoute = 'edit_product'"
          dark
        >Edit Product</v-btn>
        <v-btn
          color="red"
          v-if="$root.user && thisProduct && cmsRoute == 'edit_product'"
          @click="deleteProduct"
          dark
        >Delete Product</v-btn>
        <v-btn color="blue" v-if="!$root.user" dark @click="cmsRoute = 'login'">Login</v-btn>
      </div>
      <div>
        <v-btn color="yellow" v-if="$root.user">Logout</v-btn>
      </div>
    </v-layout>

    <v-container
      fluid
      fill-height
      v-if="cmsRoute != 'home'"
      max-height="80vh"
      style="overflow-y: auto"
    >
      <v-layout align-center justify-center>
        <Login v-if="cmsRoute == 'login'"/>
        <CreateUser v-if="cmsRoute == 'create_user'"/>
        <CreateProduct v-if="cmsRoute == 'create_product'"/>
        <EditProduct v-if="cmsRoute == 'edit_product'"/>
      </v-layout>
    </v-container>

    <v-toolbar v-if="cmsRoute == 'home'" dark color="blue">
      <v-btn icon @click="thisProduct = false" v-if="thisProduct">
        <v-icon>home</v-icon>
      </v-btn>
      <v-toolbar-side-icon @click="drawer = !drawer" v-if="!thisProduct"></v-toolbar-side-icon>

      <v-toolbar-title class="white--text">Sport Check</v-toolbar-title>

      <v-spacer></v-spacer>
      <v-flex v-if="!thisProduct">
        <v-text-field label="Solo" solo light v-model="search_text" placeholder="Search"></v-text-field>
      </v-flex>
      <v-btn icon @click="drawer = !drawer" v-if="!thisProduct">
        <v-icon>search</v-icon>
      </v-btn>
    </v-toolbar>

    <div v-if="cmsRoute == 'home'">
      <Products v-if="!thisProduct"/>
      <Product v-if="thisProduct"/>
    </div>
  </div>
</template>

<script>
import Products from './components/Products';
import Product from './components/Product';

import Login from './components/CMS/login';
import CreateUser from './components/CMS/create_user';
import CreateProduct from './components/CMS/create_product';
import EditProduct from './components/CMS/edit_product';

import axios from 'axios';

export default {
    name: 'CMS',
    data() {
        return {
            cmsRoute: 'home',
            drawer: window.innerWidth > 400,
            search: window.innerWidth > 400,
            data: {
                products: [],
            },
            thisProduct: false,
            page: 1,
            products_per_page: 20,
            search_text: '',
            catagories: [],
            all_catagories: [],
        };
    },
    methods: {
        fetchProducts() {
            axios
                .get(
                    this.$root.home +
                        'index.php' +
                        '?product_per_page=' +
                        this.products_per_page +
                        '&page=' +
                        this.page +
                        (this.search_text.length > 0
                            ? '&search=' + encodeURI(this.search_text)
                            : '') +
                        (this.catagories.length > 0
                            ? '&category_ids=' + this.catagories.join(',')
                            : ''),
                )
                .then(({ data }) => (this.data = data));
        },
        fetchCategoies() {
            axios
                .get(this.$root.home + 'index.php' + '?catagories=true')
                .then(({ data }) => {
                    this.all_catagories = data;
                });
        },
        deleteProduct() {
            if (window.confirm('Do you really want to delete this product')) {
                var querystring = require('querystring');
                axios
                    .delete(this.$root.home + 'index.php', {
                        data: querystring.stringify({
                            product_id: this.thisProduct.product_id,
                        }),
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                    })
                    .then(({ data }) => {
                        if (data.success) {
                            this.cmsRoute = 'home';
                        }
                    });
            }
        },
    },
    components: {
        Products,
        Product,
        Login,
        CreateUser,
        CreateProduct,
        EditProduct,
    },
    mounted() {
        this.fetchProducts();
        this.fetchCategoies();
    },
    watch: {
        page() {
            this.fetchProducts();
        },
        products_per_page() {
            this.fetchProducts();
        },
        search_text() {
            this.fetchProducts();
        },
        catagories() {
            this.fetchProducts();
        },
    },
};
</script>

<style>
html,
body {
    overflow: hidden;
}
* {
    font-family: 'Roboto', sans-serif;
}
</style>

