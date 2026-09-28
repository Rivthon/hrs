<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class DepartmentManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_hr_can_create_child_department_with_normalized_code(): void
    {
        $hr = User::factory()->create(['role' => 'hr']);
        $parent = Department::factory()->create();

        $this->actingAs($hr)->post(route('departments.store'), [
            'parent_id' => $parent->id,
            'code' => '  ti-01  ',
            'name' => 'Teknologi Informasi',
            'type' => 'work_unit',
            'is_active' => '1',
        ])->assertRedirect(route('departments.index'));

        $this->assertDatabaseHas('departments', [
            'parent_id' => $parent->id,
            'code' => 'TI-01',
            'name' => 'Teknologi Informasi',
            'is_active' => true,
        ]);
    }

    public function test_staff_cannot_access_department_management(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);

        $this->actingAs($staff)->get(route('departments.index'))->assertForbidden();
    }

    public function test_department_cannot_be_moved_below_its_descendant(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $parent = Department::factory()->create();
        $child = Department::factory()->create(['parent_id' => $parent->id]);

        $this->actingAs($admin)->put(route('departments.update', $parent), [
            'parent_id' => $child->id,
            'code' => $parent->code,
            'name' => $parent->name,
            'type' => $parent->type,
            'is_active' => '1',
        ])->assertSessionHasErrors('parent_id');

        $this->assertNull($parent->refresh()->parent_id);
    }

    public function test_department_with_employees_cannot_be_deleted(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $department = Department::factory()->create();
        Employee::factory()->for($department)->create();

        $this->actingAs($admin)->delete(route('departments.destroy', $department))
            ->assertSessionHasErrors('department');

        $this->assertModelExists($department);
    }

    public function test_department_name_is_escaped_in_list(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Department::factory()->create(['name' => '<script>alert(1)</script>']);

        $this->actingAs($admin)->get(route('departments.index'))
            ->assertSee('&lt;script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }
}
