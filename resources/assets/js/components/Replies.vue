<template>
    <div>
        <div v-for="(reply, index) in items"  :key="reply.id">
            <reply  :data="reply" @deleted="remove(index)" ></reply>
        </div>
        <paginator :data-set="dataSet" @changed="fetch"></paginator>
        <p v-if="locked">
            Thread has been locked by Administrator. No more replies can be added!
        </p>
        <new-reply v-else @created="add"></new-reply>
    </div>
</template>

<script>
import Reply from './Reply.vue';
import NewReply from './NewReply.vue';
import collection from '../mixins/collection';
export default {
    props: ['locked'],
    components: { Reply, NewReply },
    mixins: [ collection ],
    data() {
        return {
            dataSet: false
        }
    },
    methods: {
        fetch(page) {
            axios.get(this.url(page))
                .then(this.refresh);
        },
        url(page) {
            if(!page) {
                let query = location.search.match(/page=(\d+)/);
                page = query ? query[1] : 1;
            }
            return location.pathname + "/replies?page=" + page;
        },
        refresh({data}) {
            this.items = data.data;
            this.dataSet = data;
        }
    },
    created() {
        this.fetch();
    },
}
</script>

