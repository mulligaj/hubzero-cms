<?php

namespace Gitlab\Model;

use Gitlab\Client;

class Issue extends AbstractModel
{
    protected static $_properties = array(
        'id',
        'iid',
        'project_id',
        'title',
        'description',
        'labels',
        'milestone',
        'assignee',
        'author',
        'closed',
        'updated_at',
        'created_at',
        'project',
        'state'
    );

    public static function fromArray(Client $client, Project $project, array $data)
    {
        $issue = new Issue($project, $data['id'], $client);

        if (isset($data['author'])) {
            $data['author'] = User::fromArray($client, $data['author']);
        }

        if (isset($data['assignee'])) {
            $data['assignee'] = User::fromArray($client, $data['assignee']);
        }

        return $issue->hydrate($data);
    }

    public function __construct(Project $project, $id = null, Client $client = null)
    {
        $this->setClient($client);

        $this->project = $project;
        $this->id = $id;
    }

    public function show()
    {
        $data = $this->api('issues')->show($this->project->id, $this->id);

        return Issue::fromArray($this->getClient(), $this->project, $data);
    }

    public function update(array $params)
    {
        $data = $this->api('issues')->update($this->project->id, $this->id, $params);

        return Issue::fromArray($this->getClient(), $this->project, $data);
    }

    public function close($comment = null)
    {
        if ($comment) {
            $this->addComment($comment);
        }
        
        return $this->update(array(
            'state_event' => 'close'
        ));
    }

    public function open()
    {
        return $this->update(array(
            'state_event' => 'reopen'
        ));
    }

    public function addComment($body)
    {
        $data = $this->api('issues')->addComment($this->project->id, $this->id, array(
            'body' => $body
        ));

        return Note::fromArray($this->getClient(), $this, $data);
    }

}
