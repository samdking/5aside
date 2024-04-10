<?php

namespace App\Queries;

class TeammatesQuery extends CompetitorQuery
{
  protected $joinWith = 'teams';

  protected function formWithCompetitor($form, $teammate)
  {
    return $form->map(function($match) use ($teammate) {
      return $match && $match->teammates->map->id->contains($teammate->id) ? $match : null;
    });
  }
}
