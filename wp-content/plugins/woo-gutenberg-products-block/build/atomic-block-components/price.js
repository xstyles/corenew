(window.webpackWcBlocksJsonp=window.webpackWcBlocksJsonp||[]).push([[11],{105:function(e,c,r){"use strict";var t=r(0),a=r(1),n=r(41),l=r(4),o=r.n(l),i=r(35);r(156);const s=e=>{let{currency:c,maxPrice:r,minPrice:l,priceClassName:s,priceStyle:u={}}=e;return Object(t.createElement)(t.Fragment,null,Object(t.createElement)("span",{className:"screen-reader-text"},Object(a.sprintf)(
/* translators: %1$s min price, %2$s max price */
Object(a.__)("Price between %1$s and %2$s","woo-gutenberg-products-block"),Object(i.formatPrice)(l),Object(i.formatPrice)(r))),Object(t.createElement)("span",{"aria-hidden":!0},Object(t.createElement)(n.a,{className:o()("wc-block-components-product-price__value",s),currency:c,value:l,style:u})," — ",Object(t.createElement)(n.a,{className:o()("wc-block-components-product-price__value",s),currency:c,value:r,style:u})))},u=e=>{let{currency:c,regularPriceClassName:r,regularPriceStyle:l,regularPrice:i,priceClassName:s,priceStyle:u,price:m}=e;return Object(t.createElement)(t.Fragment,null,Object(t.createElement)("span",{className:"screen-reader-text"},Object(a.__)("Previous price:","woo-gutenberg-products-block")),Object(t.createElement)(n.a,{currency:c,renderText:e=>Object(t.createElement)("del",{className:o()("wc-block-components-product-price__regular",r),style:l},e),value:i}),Object(t.createElement)("span",{className:"screen-reader-text"},Object(a.__)("Discounted price:","woo-gutenberg-products-block")),Object(t.createElement)(n.a,{currency:c,renderText:e=>Object(t.createElement)("ins",{className:o()("wc-block-components-product-price__value","is-discounted",s),style:u},e),value:m}))};c.a=e=>{let{align:c,className:r,currency:a,format:l="<price/>",maxPrice:i,minPrice:m,price:p,priceClassName:b,priceStyle:d,regularPrice:g,regularPriceClassName:j,regularPriceStyle:O}=e;const _=o()(r,"price","wc-block-components-product-price",{["wc-block-components-product-price--align-"+c]:c});l.includes("<price/>")||(l="<price/>",console.error("Price formats need to include the `<price/>` tag."));const y=g&&p!==g;let N=Object(t.createElement)("span",{className:o()("wc-block-components-product-price__value",b)});return y?N=Object(t.createElement)(u,{currency:a,price:p,priceClassName:b,priceStyle:d,regularPrice:g,regularPriceClassName:j,regularPriceStyle:O}):void 0!==m&&void 0!==i?N=Object(t.createElement)(s,{currency:a,maxPrice:i,minPrice:m,priceClassName:b,priceStyle:d}):p&&(N=Object(t.createElement)(n.a,{className:o()("wc-block-components-product-price__value",b),currency:a,value:p,style:d})),Object(t.createElement)("span",{className:_},Object(t.createInterpolateElement)(l,{price:N}))}},138:function(e,c){},156:function(e,c){},259:function(e,c,r){"use strict";r.r(c);var t=r(0),a=(r(10),r(4)),n=r.n(a),l=r(105),o=r(35),i=r(29),s=r(7),u=r(77),m=r(54);c.default=Object(m.withProductDataContext)(e=>{var c,r;let{className:a,align:m,fontSize:p,customFontSize:b,saleFontSize:d,customSaleFontSize:g,color:j,customColor:O,saleColor:_,customSaleColor:y}=e;const{parentClassName:N}=Object(i.useInnerBlockLayoutContext)(),{product:v}=Object(i.useProductDataContext)(),f=n()(a,{[N+"__product-price"]:N});if(!v.id)return Object(t.createElement)(l.a,{align:m,className:f});const P=Object(s.getColorClassName)("color",j),C=Object(s.getFontSizeClass)(p),S=Object(s.getColorClassName)("color",_),w=Object(s.getFontSizeClass)(d),x=n()({"has-text-color":j||O,"has-font-size":p||b,[P]:P,[C]:C}),E=n()({"has-text-color":_||y,"has-font-size":d||g,[S]:S,[w]:w}),k={color:O,fontSize:b},h={color:y,fontSize:g},z=v.prices,F=Object(o.getCurrencyFromPriceResponse)(z),D=z.price!==z.regular_price,T=D?n()({[N+"__product-price__value"]:N,[E]:Object(u.b)()}):n()({[N+"__product-price__value"]:N,[x]:Object(u.b)()}),B=D?h:k;return Object(t.createElement)(l.a,{align:m,className:f,currency:F,price:z.price,priceClassName:T,priceStyle:Object(u.b)()?B:{},minPrice:null==z||null===(c=z.price_range)||void 0===c?void 0:c.min_amount,maxPrice:null==z||null===(r=z.price_range)||void 0===r?void 0:r.max_amount,regularPrice:z.regular_price,regularPriceClassName:n()({[N+"__product-price__regular"]:N,[x]:Object(u.b)()}),regularPriceStyle:Object(u.b)()?k:{}})})},41:function(e,c,r){"use strict";var t=r(5),a=r.n(t),n=r(0),l=r(132),o=r(4),i=r.n(o);r(138);const s=e=>({thousandSeparator:e.thousandSeparator,decimalSeparator:e.decimalSeparator,decimalScale:e.minorUnit,fixedDecimalScale:!0,prefix:e.prefix,suffix:e.suffix,isNumericString:!0});c.a=e=>{let{className:c,value:r,currency:t,onValueChange:o,displayType:u="text",...m}=e;const p="string"==typeof r?parseInt(r,10):r;if(!Number.isFinite(p))return null;const b=p/10**t.minorUnit;if(!Number.isFinite(b))return null;const d=i()("wc-block-formatted-money-amount","wc-block-components-formatted-money-amount",c),g={...m,...s(t),value:void 0,currency:void 0,onValueChange:void 0},j=o?e=>{const c=+e.value*10**t.minorUnit;o(c)}:()=>{};return Object(n.createElement)(l.a,a()({className:d,displayType:u},g,{value:b,onValueChange:j}))}}}]);