<?php
/**
 * Task API Service
 * Author: Santoshchandan.it@gmail.com
*/

namespace Santoshchandan\TasksApi\Services;

use Santoshchandan\TasksApi\Models\Task;

class TaskService
{
    /**
     * Get Tasks
     * @param $page
     * @param $limit
     */
    public function getTasks(int $page = 1, int $limit = 10)
    {
        $offset = ($page - 1) * $limit;
        return Task::offset($offset)->limit($limit)->orderBy('id', 'desc')->get();
    }

    /**
     * Get Task by id
     * @param $id
     */
    public function getTaskById(int $id)
    {
        return Task::find($id);
    }

    /**
     * Create task
     * @param $data
     */
    public function createTask(array $data)
    {
        return Task::create([
            'title' => $data['title'],
            'description' => $data['description'] ?? '',
            'completed' => $data['completed'] ?? false,
            'created_at' => date('Y-m-d H:i:s'), // Manually set the created_at
        ]);
    }

    /**
     * Update Task
     * @param $task
     * @param $data
     */
    public function updateTask(Task $task, array $data)
    {
        return $task->update([
            'title' => $data['title'] ?? $task->title,
            'description' => $data['description'] ?? $task->description,
            'completed' => $data['completed'] ?? $task->completed,
        ]);
    }

    /**
     * Delete Task
     * @param $task
     */
    public function deleteTask(Task $task)
    {
        return $task->delete();
    }
}
