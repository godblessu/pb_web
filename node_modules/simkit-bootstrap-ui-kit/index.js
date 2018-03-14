import Button from './src/Button'; 
import Loading from './src/Loading';
import OffsetPaginator from './src/OffsetPaginator';
import ProcessClickButton from './src/ProcessClickButton';

export default {install (Vue) {
  Vue.component('bootstrap-ui-kit:'+Button.name, Button);
  Vue.component('bootstrap-ui-kit:'+Loading.name, Loading);
  Vue.component('bootstrap-ui-kit:'+OffsetPaginator.name, OffsetPaginator);
  Vue.component('bootstrap-ui-kit:'+ProcessClickButton.name, ProcessClickButton);
}};
