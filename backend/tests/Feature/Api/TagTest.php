<?php

use App\Models\Tag;

describe('GET /api/v1/tags', function () {
    it('returns only active tags sorted alphabetically by name', function () {
        Tag::factory()->createMany([
            ['name' => 'Zend',   'slug' => 'zend',   'is_active' => true],
            ['name' => 'Alpine', 'slug' => 'alpine', 'is_active' => true],
            ['name' => 'Beta',   'slug' => 'beta',   'is_active' => false],
        ]);

        $this->getJson('/api/v1/tags')
            ->assertOk()
            ->assertJson(['success' => true, 'message' => 'Tags retrieved'])
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.name', 'Alpine')
            ->assertJsonPath('data.1.name', 'Zend')
            ->assertJsonStructure([
                'data' => [['id', 'name', 'slug']],
            ]);
    });

    it('returns an empty list when no active tags exist', function () {
        Tag::factory()->count(3)->create(['is_active' => false]);

        $this->getJson('/api/v1/tags')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    });

    it('returns an empty list when there are no tags at all', function () {
        $this->getJson('/api/v1/tags')
            ->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonCount(0, 'data');
    });
});
