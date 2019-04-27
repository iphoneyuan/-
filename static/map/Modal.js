// 初始化
var Modal = null;

window.onload = function () {
  Modal = new ModalFn({});
}

function ModalFn(config) {
  // 指向
  var This = this;

  // 渲染元素
  this.html = function (data, icon) {
    // console.log('渲染元素')
    var title = data.title ? '<span class="confirm-title">' + data.title + '</span>' : '';
    var content = data.content ? '<div class="confirm-content"><div class="content-inserted">' + data.content + '</div></div>' : '';
    var btns = '';
    var btnArr = [];
    var timeId = '';
    if (data.btn) {
      btns += '<div class="confirm-btns">';
      for (var i = 0; i < data.btn.length; i++) {
        var type = data.btn[i].type ? data.btn[i].type : 'btn-ghost';
        var onlyCode = 'key' + (parseInt(Math.random() * 99999999999999));
        if( data.btn[i].time ){
          var onlyCodeTime = 'time' + (parseInt(Math.random() * 99999999999999));
          var timeObj = '<i id="'+onlyCodeTime+'">'+data.btn[i].time+'</i>';
          timeId = onlyCodeTime;
        }else{
          var timeObj = '';
        }
        btns += ' <button class="Mbtn ' + type + '" id="' + onlyCode + '"><span>' + data.btn[i].text + '</span>'+timeObj+'</button> ';
        btnArr.push(onlyCode);
      }
      btns += '</div">';
    }
    


    var html = '<div class="Modal"><div class="Modal-mask"></div>';
    
    var wrap = '<div class="Modal-wrap">' +
    '<div class="Modal-content">' +
    '<div class="Modal-body">' +
    '<div style="zoom: 1; overflow: hidden;">' +
    '<div class="confirm-body">' +
    '<i class="confirm-icon ' + icon + '"></i>' +
    title +
    content +
    '</div>' +
    btns +
    '</div></div></div>';

    var htmle = '</div>';

    if (document.querySelector('.Modal')) {
      // 移除
      var oModalWrap = document.querySelector('.Modal-wrap');
      oModalWrap.parentNode.removeChild(oModalWrap);
      // 插入
      document.querySelector('.Modal').insertAdjacentHTML('beforeend', wrap);
    }else{
      // 插入
      document.querySelector('body').insertAdjacentHTML('beforeend', html+wrap+htmle);
    }

    // 绑定点击事件
    if (data.btn) {
      for (var i = 0; i < btnArr.length; i++) {
        (function (j) {
          document.querySelector('#' + btnArr[i]).onclick = function () {
            data.btn[j].onFn();
            This.remove();
          };
        })(i);
      }
    }

    // 绑定倒计时
    if(timeId){
      var ModalCountdown = setInterval(function(){
        var obj = document.querySelector('#' + timeId);
        if(obj){
          if(parseInt(obj.innerHTML)>1){
            obj.innerHTML = parseInt(obj.innerHTML)-1;
          }else{
            for (var i = 0; i < data.btn.length; i++) {
              (function (j) {
                if( data.btn[j].time ){
                  data.btn[j].onFn();
                  This.remove();
                }
              })(i);
            }
            clearInterval(ModalCountdown);
          }
        }else{
          clearInterval(ModalCountdown);
        }
      },1000);
    }

    // 绑定遮罩层事件
    if(data.mask){
      document.querySelector('.Modal-mask').onclick = function () {
        console.log(data.mask)
        This.remove();
      };
    }

  }

  // 加载中
  this.loading = function (data) {
    this.html(data, 'loading-icon');
  }

  // 成功弹层
  this.success = function (data) {
    this.html(data, 'success-icon');
  }

  // 失败弹层
  this.error = function (data) {
    this.html(data, 'error-icon');
  }

  // 警告弹层
  this.warning = function (data) {
    this.html(data, 'warning-icon');
  }

  // 提示弹层
  this.info = function (data) {
    this.html(data, 'info-icon');
  }

  // 疑问弹层
  this.doubt = function (data) {
    this.html(data, 'doubt-icon');
  }

  // 文本弹层
  this.text = function (data) {
    this.html(data);
  }

  // 移除弹层
  this.remove = function () {
    var oModal = document.querySelector('.Modal');
    if(oModal){
      oModal.querySelector('.Modal-wrap').className += ' Modal-leave';
      oModal.querySelector('.Modal-mask').className += ' Modal-Mask-leave';
      setTimeout(function () {
        oModal.parentNode.removeChild(oModal);
      }, 300);
    }
  }
}