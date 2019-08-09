; bloco principal
main:
; chama a função
	bl funcao
; volta da funçao
	mov r1, r0
; sai
	b fim
funcao:
	mov r2, #100
	mov r3, #200
	and r4, r2, r3
	mul r4, r4, r4
	mov r0, r4
	mov pc, r14
fim: