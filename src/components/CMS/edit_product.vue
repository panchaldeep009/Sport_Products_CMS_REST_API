<template>
  <v-layout row wrap style="max-height: 90vh; overflow-y: auto">
    <v-flex sm-12 md-6 lg-4>
      <v-card style="padding: 2em">
        <p class="subheading">Product Name</p>
        <p class="subheading">{{messages.productName}}</p>
        <v-text-field
          v-model="productname"
          :rules="[rules.required]"
          type="text"
          name="product"
          label="Product Name"
          hint="Required."
          outline
        ></v-text-field>
        <v-btn @click="saveProductName" color="yellow" light>Save</v-btn>
      </v-card>
      <v-card style="padding: 2em" v-for="(photos, index) in product.media" :key="index">
        <p class="subheading">Change this Image</p>
        <img :src="photos.media_src" width="300">
        <input type="file" :id="'media_file_'+photos.media_id" accept="image/*">
        <br>
        <v-btn
          @click.prevent="deleteData({media_id : photos.media_id, product_media: photos.product_id})"
          color="red"
          dark
        >Delete</v-btn>
        <v-btn @click.prevent="saveProductMedia(photos.media_id)" color="yellow" light>Save</v-btn>
      </v-card>
      <v-card style="padding: 2em">
        <p class="subheading">Add new Image</p>
        <p class="subheading">{{messages.newProductMedia}}</p>
        <input type="file" id="new_image" accept="image/*">
        <br>
        <v-btn @click="newProductMedia" color="yellow" light>Save</v-btn>
      </v-card>
    </v-flex>
    <v-flex sm-12 md-6 lg-4>
      <v-card style="padding: 2em">
        <p class="subheading">Product Information</p>
        <p class="subheading">{{messages.productInfo}}</p>
        <v-textarea v-model="decription" name="input-7-1" label="Product Decription" outline></v-textarea>
        <br>
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
        <v-btn @click="saveProductInfo" color="yellow" light>Save</v-btn>
      </v-card>
    </v-flex>
    <v-flex sm-12 md-6 lg-4>
      <v-card style="padding: 2em">
        <p class="subheading">Change Catagories</p>
        <p class="subheading">{{messages.productCat}}</p>
        <div v-for="(cat, index) in product.catagories_id" :key="index">
          <select :id="'cat_'+cat.products_category_id">
            <option
              v-for="(thisCat, index) in $parent.all_catagories"
              :value="thisCat.category_id"
              :key="index"
              :selected="thisCat.category_id == cat.category_id"
            >{{thisCat.category_name}}</option>
          </select>
          <v-btn
            @click.prevent="saveProductCategory(cat.products_category_id)"
            color="yellow"
            light
          >Save</v-btn>
          <v-btn
            @click.prevent="deleteData({products_category_id : cat.products_category_id})"
            color="red"
            dark
          >Delete</v-btn>
        </div>
        <br>
        <br>
        <p class="subheading">Add new Catagories</p>
        <select v-model="newCategory">
          <option
            v-for="(thisCat, index) in $parent.all_catagories"
            :key="index"
            value{{thisCat.category_id}}
          >{{thisCat.category_name}}</option>
        </select>
        <v-btn @click="newProductCategory" color="yellow" light>Add</v-btn>
      </v-card>
    </v-flex>
  </v-layout>
</template>

<script>
import axios from 'axios';
var querystring = require('querystring');

export default {
    data() {
        return {
            product: false,
            productname: this.$parent.thisProduct.product_name,
            decription: '',
            categories: [],
            features: '',
            price: '',
            error: '',
            newCategory: '',
            messages: {
                productName: '',
                productInfo: '',
                productCat: '',
            },
            rules: {
                required: value => !!value || 'Required.',
                min: v => v.length >= 8 || 'Min 8 characters',
            },
        };
    },
    methods: {
        deleteData(data) {
            if (window.confirm('Do you really want to delete this ?')) {
                axios
                    .delete(this.$root.home + 'index.php', {
                        data: querystring.stringify(data),
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                    })
                    .then(({ data }) => {
                        if (data.error) {
                            alert(data.error);
                        } else {
                            this.getTheProduct();
                        }
                        this.getTheProduct();
                    });
            }
        },
        sendEdit(data, msg) {
            axios
                .put(
                    this.$root.home + 'index.php',
                    querystring.stringify(data),
                    {
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                    },
                )
                .then(({ data }) => {
                    if (data.success) {
                        this.messages[msg] = data.success;
                    } else {
                        this.messages[msg] = data.error;
                    }
                });
        },
        saveProductName() {
            this.sendEdit(
                {
                    product_id: this.$parent.thisProduct.product_id,
                    product_name: this.productname,
                },
                'productName',
            );
        },
        saveProductInfo() {
            this.sendEdit(
                {
                    information_id: this.product.info[0].information_id,
                    description: this.decription,
                    features: this.features,
                    price: this.price,
                },
                'productInfo',
            );
        },
        saveProductMedia(id) {
            let formData = new FormData();
            formData.append(
                'photo',
                document.getElementById('media_file_' + id).files[0],
            );
            formData.append('media_id', id);
            formData.append('product_id', this.product.info[0].information_id);
            axios
                .post(this.$root.home + 'index.php', formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                    },
                    withCredentials: true,
                })
                .then(({ data }) => {
                    if (data.success) {
                        this.getTheProduct();
                    } else {
                        alert(data.error);
                    }
                });
        },
        saveProductCategory(id) {
            this.sendEdit(
                {
                    products_category_id: id,
                    category_id: document.getElementById('cat_' + id).value,
                    product_id: this.$parent.thisProduct.product_id,
                },
                'productCat',
            );
        },
        newProductMedia() {
            let formData = new FormData();
            formData.append(
                'photo',
                document.getElementById('new_image').files[0],
            );
            formData.append('product_id', this.$parent.thisProduct.product_id);
            axios
                .post(this.$root.home + 'index.php', formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                    },
                    withCredentials: true,
                })
                .then(({ data }) => {
                    if (data.success) {
                        this.getTheProduct();
                    } else {
                        alert(data.error);
                    }
                });
        },
        newProductCategory() {
            let formData = new FormData();
            formData.append('category_id', this.newCategory);
            formData.append('product_id', this.$parent.thisProduct.product_id);
            axios
                .post(this.$root.home + 'index.php', formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                    },
                    withCredentials: true,
                })
                .then(({ data }) => {
                    if (data.success) {
                        this.getTheProduct();
                    } else {
                        alert(data.error);
                    }
                });
        },
        getTheProduct() {
            axios
                .get(
                    this.$root.home +
                        'index.php' +
                        '?product_id=' +
                        this.$parent.thisProduct.product_id,
                )
                .then(({ data }) => {
                    this.product = data;
                    this.decription = data.info[0].description;
                    // categories: [],
                    this.features = data.info[0].features.join(';');
                    this.price = data.info[0].price;
                });
        },
    },
    mounted() {
        this.getTheProduct();
    },
};
</script>