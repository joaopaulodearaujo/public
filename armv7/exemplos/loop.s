; soma os n primeiros números e armazena em R2
; também copia esse número para a memória na posição R10 
; e o seu dobro para a posição R12 + 1024
main:
	MOV R1, #4
	MOV R2, #0
loop:
; condição de saída do loop
	CMP R1, #0
	BEQ endloop
	ADD R2, R2, R1
	SUBS R1, R1, #1
	B loop
endloop:
	MOV R10, #512
	STR R2, [R10]
	MOV R1, #2
	MOV R12, #0
	MUL R3, R1, R2
	STR R3, [R12, #1024]
	MOV R0, PC
