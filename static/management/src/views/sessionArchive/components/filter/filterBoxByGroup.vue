<template>
    <div class="zm-filter-box">
        <div class="zm-filter-item">
            <span class="zm-filter-label">群聊名称：</span>
            <div class="filter-item-content">
                <a-input-search
                    v-model:value="filterData.keyword"
                    @search="search"
                    allowClear
                    placeholder="请输入群聊名称进行搜索"
                    size="small"/>
            </div>
        </div>
    </div>
</template>

<script setup>
import {reactive} from 'vue';

const emit = defineEmits(['change'])
const filterData = reactive({
    keyword: '',
})

const search = () => {
    filterData.keyword = filterData.keyword.trim()
    emit('change', filterData)
}
</script>

<style scoped lang="less">
.zm-filter-box {
    padding: 8px 60px 0 16px;
    flex-wrap: wrap;
    height: 41px;
    overflow: hidden;
    border-bottom: 1px solid rgba(5, 5, 5, 0.06);

    .zm-filter-item {
        margin-bottom: 12px;

        .filter-item-content {
            width: 192px;
            flex-shrink: 0;
        }
    }
}
</style>
