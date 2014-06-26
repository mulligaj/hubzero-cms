<?php

namespace Gitlab\Api;

class Milestones extends AbstractApi
{
    public function all($project_id, $page = 1, $per_page = self::PER_PAGE)
    {
        return $this->get('projects/'.urlencode($project_id).'/milestones', array(
            'page' => $page,
            'per_page' => $per_page
        ));
    }

    public function show($project_id, $milestone_id)
    {
        return $this->get('projects/'.urlencode($project_id).'/milestones/'.urlencode($milestone_id));
    }

    public function create($project_id, array $params)
    {
        return $this->post('projects/'.urlencode($project_id).'/milestones', $params);
    }

    public function update($project_id, $milestone_id, array $params)
    {
        return $this->put('projects/'.urlencode($project_id).'/milestones/'.urlencode($milestone_id), $params);
    }

}
