<template>
  <v-card
    tag="form"
    @submit.prevent="create_product"
    style="overflow-y: auto; padding: 5em"
    min-width="400"
    max-height="90vh"
    dark
    color="cyan"
  >
    <h1 class="display-2 mb-4">Create Product</h1>
    <h1 class="subheading mb-4">{{error}}</h1>
    <p class="subheading">Product Image</p>
    <input type="file" id="file" ref="file" accept="image/*">
    <br>
    <br>
    <v-text-field
      v-model="productname"
      :rules="[rules.required]"
      type="text"
      name="product"
      label="Product Name"
      hint="Required."
      outline
    ></v-text-field>
    <v-textarea v-model="decription" name="input-7-1" label="Product Decription" outline></v-textarea>

    <p class="subheading">Select Catagories</p>
    <v-list class="pa-0" style="overflow-y: auto; max-height: 200px" light>
      <v-list-tile
        v-for="category in $parent.all_catagories.slice(0)
            .filter(({category_name}) => category_name == 'Men' || category_name == 'Women' || category_name == 'Boys' || category_name == 'Girls')"
        :key="category.category_id"
      >
        <v-list-tile-content>
          <v-checkbox
            color="pink"
            v-model="categories"
            :label="category.category_name"
            :value="category.category_id"
          ></v-checkbox>
        </v-list-tile-content>
      </v-list-tile>

      <v-divider></v-divider>

      <v-list-tile
        v-for="category in $parent.all_catagories.slice(0)
            .filter(({category_name}) => category_name !== 'Men' && category_name !== 'Women' && category_name != 'Boys' && category_name != 'Girls')"
        :key="category.category_id"
      >
        <v-list-tile-content>
          <v-checkbox
            color="pink"
            v-model="categories"
            :label="category.category_name"
            :value="category.category_id"
          ></v-checkbox>
        </v-list-tile-content>
      </v-list-tile>
    </v-list>
    <br>
    <v-textarea v-model="features" name="input-7-1" label="Product features" outline></v-textarea>
    <p class="subheading">Product Features can be seprated by ';'</p>
    <br>
    <br>
    <v-text-field
      v-model="price"
      :rules="[rules.required]"
      type="number"
      name="product"
      label="Product Price"
      hint="in CAD $"
      outline
    ></v-text-field>

    <v-btn light type="submit">Create</v-btn>
  </v-card>
</template>

<script>
import axios from 'axios';

export default {
    data() {
        return {
            show: false,
            productname: '',
            decription: '',
            categories: [],
            features: '',
            price: '',
            error: '',
            rules: {
                required: value => !!value || 'Required.',
                min: v => v.length >= 8 || 'Min 8 characters',
            },
        };
    },
    methods: {
        create_product() {
            let formData = new FormData();
            formData.append('photo', this.$refs.file.files[0]);
            formData.append('product_name', this.productname);
            formData.append('description', this.decription);
            formData.append('categories', this.categories.join(','));
            formData.append('features', this.features);
            formData.append('price', this.price);
            axios
                .post(this.$root.home + 'index.php', formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                    },
                    withCredentials: true,
                })
                .then(({ data }) => {
                    if (data.error) {
                        this.error = data.error;
                    } else {
                        this.error = 'Product created successfully';
                        this.username = '';
                        this.password = '';
                        this.name = '';
                        this.email = '';
                    }
                });
        },
    },
};
</script>