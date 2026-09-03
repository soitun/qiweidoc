<template>
    <a-modal v-model:open="visible"
        title="查看聊天记录"
        :confirm-loading="saving"
        :keyboard="!childVisible"
        width="746px">
            <MessageItem ref="messageItemRef" @showMessage="onShowMessage"/>
        <template #footer></template>

        <!-- 聊天记录弹窗 -->
        <MessageListPage ref="messageListPageRef" @visible-change="childVisible = $event" />
    </a-modal>
</template>

<script setup>
import { ref, nextTick } from 'vue'
import MessageListPage from './messageListPage.vue';
import MessageItem from './messageItem.vue';

const messageItemRef = ref(null)
const messageListPageRef = ref(null)
const visible = ref(false)
const saving = ref(false)
const childVisible = ref(false)
const messageList = ref([])

const onShowMessage = (list, title) => {
    nextTick(() => {
        if (messageListPageRef.value) {
            messageListPageRef.value.show(list, title)
        }
    })
}


const show = (messageListData) => {
    messageList.value = messageListData
    visible.value = true
    saving.value = false
    nextTick(() => {
        if (messageItemRef.value) {
            messageItemRef.value.show(messageListData)
        }
    })
}

defineExpose({ show })
</script>

<style scoped lang="less">
/deep/ .ant-form-item-label {
  line-height: 28px;
}

/deep/ .ant-form-item-control {
  line-height: 28px;
}
</style>
