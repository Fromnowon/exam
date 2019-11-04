import Vue from 'vue'

import 'normalize.css/normalize.css' // A modern alternative to CSS resets

import ElementUI from 'element-ui'
import 'element-ui/lib/theme-chalk/index.css'
import locale from 'element-ui/lib/locale/lang/en' // lang i18n

import '@/styles/index.scss' // global css

import App from './App'
import store from './store'
import router from './router'
import axios from 'axios'

import '@/icons' // icon
import '@/permission' // permission control

import hljs from 'highlight.js'
import 'highlight.js/styles/solarized-light.css' //样式文件

/**
 * If you don't want to use mock-server
 * you want to use MockJs for mock api
 * you can execute: mockXHR()
 *
 * Currently MockJs will be used in the production environment,
 * please remove it before going online! ! !
 */
import {
  mockXHR
} from '../mock'
if (process.env.NODE_ENV === 'production') {
  mockXHR()
}

// set ElementUI lang to EN
Vue.use(ElementUI, {
  locale
})

Vue.config.productionTip = false
Vue.prototype.$axios = axios
Vue.prototype.api = 'http://0.0.0.0:8083/server.php'

Vue.directive('highlight', {
  bind: function (el, binding) {
    let blocks = el.querySelectorAll('pre code');
    blocks.forEach((block) => {
      hljs.highlightBlock(block)
    })
  }
})

new Vue({
  el: '#app',
  router,
  store,
  render: h => h(App)
})
