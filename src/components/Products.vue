<template>
  <v-layout row style="height : calc(100vh - 66px)">
    <v-card v-if="$parent.drawer" min-width="280">
      <v-list class="pa-0" style="overflow-y: auto; max-height: calc(100vh - 66px)">
        <v-list-tile avatar v-if="$root.admin">
          <v-list-tile-avatar color="indigo">
            <v-icon dark>account_circle</v-icon>
          </v-list-tile-avatar>

          <v-list-tile-content>
            <v-list-tile-title>John Leider</v-list-tile-title>
          </v-list-tile-content>
        </v-list-tile>

        <v-divider></v-divider>

        <v-list-tile>
          <v-list-tile-content>
            <v-list-tile-title>Product for</v-list-tile-title>
          </v-list-tile-content>
        </v-list-tile>

        <v-divider></v-divider>

        <v-list-tile
          v-for="category in $parent.all_catagories.slice(0)
            .filter(({category_name}) => category_name == 'Men' || category_name == 'Women')"
          :key="category.category_id"
        >
          <v-list-tile-content>
            <v-checkbox
              color="pink"
              v-model="$parent.catagories"
              :label="category.category_name"
              :value="category.category_id"
            ></v-checkbox>
          </v-list-tile-content>
        </v-list-tile>

        <v-divider></v-divider>

        <v-list-tile>
          <v-list-tile-content>
            <v-list-tile-title>Catagories</v-list-tile-title>
          </v-list-tile-content>
        </v-list-tile>

        <v-divider></v-divider>

        <v-list-tile
          v-for="category in $parent.all_catagories.slice(0)
            .filter(({category_name}) => category_name !== 'Men' && category_name !== 'Women')"
          :key="category.category_id"
        >
          <v-list-tile-content>
            <v-checkbox
              color="pink"
              v-model="$parent.catagories"
              :label="category.category_name"
              :value="category.category_id"
            ></v-checkbox>
          </v-list-tile-content>
        </v-list-tile>
      </v-list>
    </v-card>
    <div style="overflow-y: auto">
      <v-layout row justify-space-between style="padding: 25px">
        <v-flex xs6>
          <v-select
            v-if="$parent.data.total_page"
            width="50px"
            v-model="$parent.products_per_page"
            hide-details
            menu-props="auto"
            :items="[10, 15, 25, 50]"
            label="Items per page"
          ></v-select>
        </v-flex>
        <v-pagination
          v-if="$parent.data.total_page"
          v-model="$parent.page"
          :length="$parent.data.total_page"
          :total-visible="5"
          color="cyan"
        ></v-pagination>
      </v-layout>
      <v-layout row wrap>
        <v-flex
          xs12
          sm6
          md4
          lg3
          xl2
          v-for="product in $parent.data.products"
          :key="product.product_id"
          style="padding: 1em"
        >
          <v-card @click="$parent.thisProduct = product">
            <v-img :src="product.image" aspect-ratio="1"></v-img>

            <v-card-title primary-title>
              <div>
                <h3 class="title mb-0">{{product.product_name}}</h3>
                <p class="headline mb-0" style="color: green">$ {{product.price}}</p>
              </div>
            </v-card-title>
          </v-card>
        </v-flex>
      </v-layout>
    </div>
  </v-layout>
</template>