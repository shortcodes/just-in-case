<script setup lang="ts">
import { computed } from 'vue'
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip'
import type { ResetLogViewModel } from '@/types/models'
import dayjs from 'dayjs'
import relativeTime from 'dayjs/plugin/relativeTime'

dayjs.extend(relativeTime)

interface ResetHistoryTableProps {
    resetHistory: ResetLogViewModel[]
}

const props = defineProps<ResetHistoryTableProps>()

const formatResetMethod = (method: string): string => {
    switch (method) {
        case 'manual_button':
            return 'Manual'
        case 'post_edit_modal':
            return 'Post-Edit'
        default:
            return method
    }
}

const formatTimestamp = (timestamp: string) => {
    return {
        relative: dayjs(timestamp).fromNow(),
        exact: dayjs(timestamp).format('MMMM D, YYYY h:mm A')
    }
}
</script>

<template>
    <div v-if="resetHistory.length > 0" class="border rounded-lg overflow-hidden">
        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>Timestamp</TableHead>
                    <TableHead>Method</TableHead>
                    <TableHead>IP Address</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-for="log in resetHistory" :key="log.id">
                    <TableCell>
                        <TooltipProvider>
                            <Tooltip>
                                <TooltipTrigger as-child>
                                    <span class="cursor-help">
                                        {{ formatTimestamp(log.createdAt).relative }}
                                    </span>
                                </TooltipTrigger>
                                <TooltipContent>
                                    <p>{{ formatTimestamp(log.createdAt).exact }}</p>
                                </TooltipContent>
                            </Tooltip>
                        </TooltipProvider>
                    </TableCell>
                    <TableCell>{{ formatResetMethod(log.resetMethod) }}</TableCell>
                    <TableCell class="font-mono text-xs">{{ log.ipAddress }}</TableCell>
                </TableRow>
            </TableBody>
        </Table>
    </div>
    <div v-else class="text-center py-8 text-gray-400 italic">
        (No reset history yet)
    </div>
</template>
