<template>
  <div class="table-container">
       <div :class="[
            'overflow-x-auto scrollbar scrollbar-thin ',
            calculatedTableStyle,
            ]">
            <vuetable
                :data="content"
                :fields="header"
                :api-mode="false"
                :css="table"
                :show-sort-icons="true"
                :per-page="3"
            ></vuetable>
       </div>
  </div>
</template>

<script>
import Vuetable from 'vuetable-2'
import {library} from '@fortawesome/fontawesome-svg-core'
import {faInfoCircle} from '@fortawesome/free-solid-svg-icons'
import {tableStyleMixin} from "@/Mixins/Styles/tableStyleMixin";
library.add(faInfoCircle)

export default {
  name: "TVueTable",
  components: {
    Vuetable
  },
  mixins: [tableStyleMixin],
  props: {
    header: {
      type: Array
    },
    content: {
      type: Array
    },
    color: {
        type: String,
        default: 'solid-blue'
    }

  },
  data() {
    return {
      css : tableStyleMixin,
      table: {
            tableWrapper: 'table w-full',
            tableHeaderClass: 'border-collapse border border-slate-400',
            tableBodyClass: 'solid-blue',
            tableClass: 'w-full',
            loadingClass: 'loading',
            ascendingIcon: 'blue chevron up icon',
            descendingIcon: 'blue chevron down icon',
            ascendingClass: 'sorted-asc',
            descendingClass: 'sorted-desc',
            sortableIcon: 'grey sort icon',
            handleIcon: 'grey sidebar icon',
        },

        pagination: {
            wrapperClass: 'ui right floated pagination menu',
            activeClass: 'active large',
            disabledClass: 'disabled',
            pageClass: 'item',
            linkClass: 'icon item',
            paginationClass: 'ui bottom attached segment grid',
            paginationInfoClass: 'left floated left aligned six wide column',
            dropdownClass: 'ui search dropdown',
            icons: {
            first: 'angle double left icon',
            prev: 'left chevron icon',
            next: 'right chevron icon',
            last: 'angle double right icon',
            }
        },

    }
  }
}
</script>


