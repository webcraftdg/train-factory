
import { Component, input, output } from '@angular/core';
import { FormGroup, ReactiveFormsModule } from '@angular/forms';

@Component({
  selector: 'app-block',
  standalone: true,
  imports: [ReactiveFormsModule],
  templateUrl: './block.html',
  styleUrl: './block.css'
})
export class BlockComponent {
  readonly form = input.required<FormGroup>();
  readonly index = input.required<number>();

  readonly remove = output<void>();
}
