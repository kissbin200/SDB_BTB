webpackJsonp([0],[,,,function(t,e,n){"use strict";var s=n(1),i=n(28),a=n(22),r=n.n(a),o=n(21),c=n.n(o),u=n(23),l=n.n(u);i.a.prototype.goback=function(){this.isBack=!0,window.history.go(-1)},s.a.use(i.a),e.a=new i.a({routes:[{path:"/",name:"PageTransition",component:l.a,children:[{path:"",name:"Index",component:r.a},{path:"/Custom",name:"Custom",component:c.a}]}]})},function(t,e,n){"use strict";var s=n(1),i=n(30),a=n(7),r=n.n(a);s.a.use(i.a),e.a=new i.a.Store({modules:{index:r.a}})},function(t,e,n){n(15);var s=n(0)(n(8),n(25),null,null);t.exports=s.exports},function(t,e,n){"use strict";Object.defineProperty(e,"__esModule",{value:!0});var s=n(1),i=n(5),a=n.n(i),r=n(3),o=n(4);s.a.config.productionTip=!1,new s.a({el:"#app",router:r.a,store:o.a,template:"<App />",components:{App:a.a}})},function(t,e){var n={state:{count:0},mutations:{increment:function(t){t.count++}},getters:{doubleCount:function(t){return 2*t.count}}};t.exports=n},function(t,e,n){"use strict";Object.defineProperty(e,"__esModule",{value:!0}),e.default={name:"app"}},function(t,e,n){"use strict";Object.defineProperty(e,"__esModule",{value:!0}),e.default={name:"hello",data:function(){return{msg:"Welcome to Your Vue.js App"}},created:function(){console.log(this.$route.query)}}},function(t,e,n){"use strict";Object.defineProperty(e,"__esModule",{value:!0}),e.default={name:"index",data:function(){return{msg:"Welcome to Your Test"}},created:function(){console.log(this.$route)}}},function(t,e,n){"use strict";Object.defineProperty(e,"__esModule",{value:!0}),e.default={data:function(){return{transitionName:"slide-left"}},beforeRouteUpdate:function(t,e,n){this.$router.isBack?(this.transitionName="slide-right",console.log("slide-right")):(this.transitionName="slide-left",console.log("slide-left")),this.$router.isBack=!1,n()}}},,,function(t,e){},function(t,e){},function(t,e){},function(t,e){},,,function(t,e,n){t.exports=n.p+"static/img/index_rebate.acdefd1.png"},function(t,e,n){n(14);var s=n(0)(n(9),n(24),"data-v-1f1a2cd8",null);t.exports=s.exports},function(t,e,n){n(17);var s=n(0)(n(10),n(27),"data-v-fd77258a",null);t.exports=s.exports},function(t,e,n){n(16);var s=n(0)(n(11),n(26),"data-v-7d6a73aa",null);t.exports=s.exports},function(t,e){t.exports={render:function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("div",{staticClass:"hello"},[t._v("\n    我是测试页面\n    "),n("br"),t._v(" "),n("router-link",{attrs:{to:"/test"}},[t._v("Go to Bar")])],1)},staticRenderFns:[]}},function(t,e){t.exports={render:function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("div",{attrs:{id:"app"}},[n("router-view")],1)},staticRenderFns:[]}},function(t,e){t.exports={render:function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("transition",{attrs:{name:t.transitionName}},[n("router-view",{staticClass:"child-view"})],1)},staticRenderFns:[]}},function(t,e,n){t.exports={render:function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("div",{attrs:{id:"index"}},[t._m(0),t._v(" "),t._m(1),t._v(" "),t._m(2),t._v(" "),n("div",{staticClass:"service"},[n("div",{staticClass:"title"},[t._v("你的服务")]),t._v(" "),n("div",{staticClass:"box"},[n("div",{staticClass:"item"},[t._v("\n      政策签约\n      ")]),t._v(" "),n("router-link",{attrs:{to:{path:"Custom",query:{}}}},[n("div",{staticClass:"item"},[t._v("客户管理")])]),t._v(" "),n("div",{staticClass:"item"},[t._v("\n      水来学院\n      ")]),t._v(" "),n("div",{staticClass:"item"},[t._v("\n      员工管理\n      ")]),t._v(" "),n("div",{staticClass:"item"},[t._v("\n      店铺推广\n      ")]),t._v(" "),n("div",{staticClass:"item"},[t._v("\n      商品管理\n      ")])],1)])])},staticRenderFns:[function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("div",{staticClass:"top"},[n("div",{staticClass:"item"},[t._v("\n      采购\n    ")]),t._v(" "),n("div",{staticClass:"item"},[t._v("\n      订单\n    ")])])},function(){var t=this,e=t.$createElement,s=t._self._c||e;return s("div",{staticClass:"rebate"},[s("div",{staticClass:"left"},[s("span",[t._v("\n        水厂返利 (元)\n      ")]),s("br"),t._v(" "),s("span",[t._v("\n        8888\n      ")])]),t._v(" "),s("div",{staticClass:"right"},[s("img",{attrs:{src:n(20),alt:""}})])])},function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("div",{staticClass:"ad"},[n("div",{staticClass:"left"},[t._v("充值优惠")]),t._v(" "),n("div",{staticClass:"middle"},[n("span",[t._v("冲的越多  送的越多")]),n("br"),t._v(" "),n("span",[t._v("冲一百送一百！")])]),t._v(" "),n("div",{staticClass:"right"},[n("span",{staticClass:"icon"},[t._v("new")]),t._v("\n    >\n    ")])])}]}}],[6]);
//# sourceMappingURL=app.22bf122bc2cd4a3cd521.js.map