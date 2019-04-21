<template>
  <v-card tag="form" @submit.prevent="login" style="padding: 2em" min-width="400" dark color="cyan">
    <h1 class="display-2 mb-4">Create User</h1>
    <h1 class="subheading mb-4">{{error}}</h1>
    <v-text-field
      v-model="username"
      :rules="[rules.required]"
      type="text"
      name="username"
      label="User Name"
      hint="Required."
      outline
    ></v-text-field>
    <v-text-field
      v-model="name"
      :rules="[rules.required]"
      type="text"
      name="name"
      label="Name"
      hint="Required."
      outline
    ></v-text-field>
    <v-text-field
      v-model="email"
      :rules="[rules.required]"
      type="email"
      name="email"
      label="Email Address"
      hint="Required."
      outline
    ></v-text-field>
    <v-text-field
      v-model="password"
      :append-icon="show ? 'visibility' : 'visibility_off'"
      :rules="[rules.required, rules.min]"
      :type="show ? 'text' : 'password'"
      name="password"
      label="Password"
      hint="At least 8 characters"
      counter
      outline
      @click:append="show = !show"
    ></v-text-field>
    <v-btn light type="submit">Create</v-btn>
  </v-card>
</template>

<script>
import axios from 'axios';
var querystring = require('querystring');

export default {
    data() {
        return {
            show: false,
            password: '',
            username: '',
            name: '',
            email: '',
            error: '',
            rules: {
                required: value => !!value || 'Required.',
                min: v => v.length >= 8 || 'Min 8 characters',
            },
        };
    },
    methods: {
        login() {
            axios
                .post(
                    this.$root.home + 'admin/index.php',
                    querystring.stringify({
                        create_user: 'true',
                        username: this.username,
                        password: this.password,
                        name: this.name,
                        email: this.email,
                    }),
                    {
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        withCredentials: true,
                    },
                )
                .then(({ data }) => {
                    if (data.error) {
                        this.error = data.error;
                    } else {
                        this.error = 'User created successfully';
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