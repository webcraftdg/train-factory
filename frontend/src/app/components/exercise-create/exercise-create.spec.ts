import { ComponentFixture, TestBed } from '@angular/core/testing';
import { ExerciseCreateComponent } from './exercise-create';

describe('ExerciseCreate', () => {
  let component: ExerciseCreateComponent;
  let fixture: ComponentFixture<ExerciseCreateComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [ExerciseCreateComponent],
    }).compileComponents();

    fixture = TestBed.createComponent(ExerciseCreateComponent);
    component = fixture.componentInstance;
    await fixture.whenStable();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
