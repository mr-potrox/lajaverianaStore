!function(modules){function __webpack_require__(moduleId){if(installedModules[moduleId])return installedModules[moduleId].exports;var module=installedModules[moduleId]={i:moduleId,l:!1,exports:{}};return modules[moduleId].call(module.exports,module,module.exports,__webpack_require__),module.l=!0,module.exports}var installedModules={};__webpack_require__.m=modules,__webpack_require__.c=installedModules,__webpack_require__.d=function(exports,name,getter){__webpack_require__.o(exports,name)||Object.defineProperty(exports,name,{configurable:!1,enumerable:!0,get:getter})},__webpack_require__.n=function(module){var getter=module&&module.__esModule?function(){return module.default}:function(){return module};return __webpack_require__.d(getter,"a",getter),getter},__webpack_require__.o=function(object,property){return Object.prototype.hasOwnProperty.call(object,property)},__webpack_require__.p="",__webpack_require__(__webpack_require__.s=0)}([function(module,exports,__webpack_require__){module.exports=__webpack_require__(1)},function(module,exports,__webpack_require__){"use strict";!function($){Drupal.behaviors.mediaEntityBrowserView={attach:function(context){var _this=this;$(".views-row",context).each(function(){var $row=$(_this),$input=$row.find(".views-field-entity-browser-select input");drupalSettings.entity_browser_widget.auto_select?$row.once("register-row-click").click(function(event){event.preventDefault(),$row.parents("form").find(".entities-list").trigger("add-entities",[[$input.val()]])}):($row[$input.prop("checked")?"addClass":"removeClass"]("checked"),$row.once("register-row-click").click(function(){$input.prop("checked",!$input.prop("checked")),$row[$input.prop("checked")?"addClass":"removeClass"]("checked")}))})}}}(jQuery)}]);