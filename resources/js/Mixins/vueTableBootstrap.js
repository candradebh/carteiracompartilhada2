import {radiusSizeMixin} from "@/Mixins/radiusSizeMixin";

export const tableStyleMixin = {
    props: {
        color: {
            type: String,
            default: 'solid-white'
        },
    },
    mixins: [radiusSizeMixin],
    data() {
        return {
            table: {
                tableWrapper: '',
                tableHeaderClass: 'mb-0',
                tableBodyClass: 'mb-0',
                tableClass: 'table table-bordered table-hover',
                loadingClass: 'loading',
                ascendingIcon: 'fa fa-chevron-up',
                descendingIcon: 'fa fa-chevron-down',
                ascendingClass: 'sorted-asc',
                descendingClass: 'sorted-desc',
                sortableIcon: 'fa fa-sort',
                detailRowClass: 'vuetable-detail-row',
                handleIcon: 'fa fa-bars text-secondary',
                renderIcon(classes, options) {
                  return `<i class="${classes.join(' ')}"></span>`
                }
              },
              pagination: {
                wrapperClass: 'pagination float-right',
                activeClass: 'active',
                disabledClass: 'disabled',
                pageClass: 'page-item',
                linkClass: 'page-link',
                paginationClass: 'pagination',
                paginationInfoClass: 'float-left',
                dropdownClass: 'form-control',
                icons: {
                  first: 'fa fa-chevron-left',
                  prev: 'fa fa-chevron-left',
                  next: 'fa fa-chevron-right',
                  last: 'fa fa-chevron-right',
                }
              }
        }
    },
    computed: {
        calculatedTableStyle() {
            let style
            /*Radius*/
            if(this.radius){
                style = this.radiusStyle + ' ';
            }else{
                style = ''
            }
            /*Shadow*/
            if(this.shadow){
                style += ' shadow-md '
            }
            /*Light Border*/
            if(this.color.includes('light')){
                style += this.tableBorderColors[this.color] + ' ';
            }
            /*Scroll*/
            style += this.scrollColors[this.color]

            return style
        }
    }
}
