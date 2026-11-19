
export interface Exercise {
  id: number | null;
  name: string;
  description: string;
}

export interface Activity {
  id: number | null;
  name: string;
  description: string;
  objective: string;
  instruction: string;
  duration: string;
  hydration: boolean;
  exercises: Exercise[];
}

export interface Block {
  id: number | null;
  name: string;
  description: string;
  objective: string;
  instruction: string;
  activities: Activity[];
}

export interface Session {
  id: number | null;
  name: string;
  description: string;
  target: string;
  blocks: Block[];
}

export function createExercise(): Exercise {
  return {
    id: null,
    name: '',
    description: ''
  };
}

export function createActivity(): Activity {
  return {
    id: null,
    name: '',
    description: '',
    objective: '',
    instruction: '',
    duration: '',
    hydration: false,
    exercises: []
  };
}

export function createBlock(): Block {
  return {
    id: null,
    name: '',
    description: '',
    objective: '',
    instruction: '',
    activities: []
  };
}

export function createSession(): Session {
  return {
    id: null,
    name: '',
    description: '',
    target: '',
    blocks: []
  };
}
