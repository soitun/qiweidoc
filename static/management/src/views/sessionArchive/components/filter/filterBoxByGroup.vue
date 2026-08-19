<template>
    <div class="zm-filter-box">
        <div class="zm-filter-item">
            <span class="zm-filter-label">群类型：</span>
            <div class="filter-item-content">
                <a-select
                    v-model:value="filterData.group_type"
                    @change="search"
                    style="width: 160px"
                    size="small">
                    <a-select-option value="">全部</a-select-option>
                    <a-select-option value="1">客户群</a-select-option>
                    <a-select-option value="2">内部群</a-select-option>
                    <a-select-option value="3">非企业客户群</a-select-option>
                    <a-select-option value="unremarked_non_enterprise">未备注非企业客户群</a-select-option>
                </a-select>
            </div>
        </div>
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
import {reactive, ref} from 'vue';

const emit = defineEmits(['change'])
const filterData = reactive({
    keyword: '',
    group_type: '',
})

const search = () => {
    filterData.keyword = filterData.keyword.trim()
    emit('change', filterData)
}
</script>

<style scoped lang="less">
.zm-filter-box {
    padding-top: 8px;
    padding-right: 60px;
    flex-wrap: wrap;
    height: 41px;
    overflow: hidden;
    border-bottom: 1px solid rgba(5, 5, 5, 0.06);

    .zm-filter-item {
        margin-bottom: 12px;
        margin-left: 16px;
    }
}

.show-date-time {
    cursor: pointer;
    color: #164799;
}
</style>
