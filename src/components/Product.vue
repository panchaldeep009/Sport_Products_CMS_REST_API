<template>
  <v-layout row style="height : calc(100vh - 66px)">
    <v-layout row wrap align-center justify-center v-if="product" style="overflow-y: auto">
      <v-flex xs12 md6 lg4 style="padding: 2em">
        <p class="title">{{categories.join(' / ')}}</p>
        <h3 class="display-1 mb-4 font-weight-thin">{{product.product_name}}</h3>
        <v-carousel>
          <v-carousel-item
            v-for="media in product.media"
            :key="media.media_id"
            :src="media.media_src"
          ></v-carousel-item>
        </v-carousel>
      </v-flex>
      <v-flex xs12 md6 lg4 style="padding: 2em">
        <h2 class="display-3 font-weight-thin" style="color: green">$ {{product.info[0].price}}</h2>
        <p class="subheading">{{product.info[0].description}}</p>
        <hr>
        <h2 class="display-1 font-weight-thin">Features</h2>

        <v-list>
          <v-list-tile v-for="(feature, i) in product.info[0].features" :key="i" avatar>
            <v-list-tile-action>
              <v-icon color="blue">star</v-icon>
            </v-list-tile-action>

            <v-list-tile-content v-html="feature"></v-list-tile-content>
          </v-list-tile>
        </v-list>
      </v-flex>
    </v-layout>
  </v-layout>
</template>
<script>
import axios from 'axios';

export default {
    data() {
        return {
            product: false,
            categories: [],
        };
    },
    methods: {
        get_this_product() {
            axios
                .get(
                    this.$root.home +
                        'index.php' +
                        '?product_id=' +
                        this.$parent.thisProduct.product_id,
                )
                .then(({ data }) => {
                    this.product = data;
                    this.categories = this.product.catagories
                        .slice(0)
                        .map(({ category_name }) => {
                            return category_name;
                        });
                });
        },
    },
    mounted() {
        this.get_this_product();
    },
};
</script>