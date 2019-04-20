<template>
  <div>
    <v-toolbar dark color="blue">
      <v-toolbar-side-icon @click="drawer = !drawer"></v-toolbar-side-icon>

      <v-toolbar-title class="white--text">Sport Check</v-toolbar-title>

      <v-spacer></v-spacer>
      <v-flex xs12 sm6 md3>
        <v-text-field label="Solo" solo light v-model="search_text" placeholder="Search"></v-text-field>
      </v-flex>
      <v-btn icon @click="drawer = !drawer">
        <v-icon>search</v-icon>
      </v-btn>
    </v-toolbar>
    <Products/>
  </div>
</template>

<script>
import Products from './components/Products';
import axios from 'axios';

export default {
    name: 'App',
    data() {
        return {
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
    },
    components: {
        Products,
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

